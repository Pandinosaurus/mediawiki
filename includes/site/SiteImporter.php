<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\Site;

use DOMDocument;
use DOMElement;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Wikimedia\RequestTimeout\TimeoutException;

/**
 * Utility for importing site entries from XML.
 *
 * For the expected format of the input, see docs/sitelist.md and docs/sitelist-1.0.xsd.
 *
 * @since 1.25
 * @ingroup Site
 * @author Daniel Kinzler
 */
class SiteImporter {

	/**
	 * @var SiteStore
	 */
	private $store;

	/**
	 * @var callable|null
	 */
	private $exceptionCallback;

	public function __construct( SiteStore $store ) {
		$this->store = $store;
	}

	/**
	 * @return callable
	 */
	public function getExceptionCallback() {
		return $this->exceptionCallback;
	}

	/**
	 * @param callable $exceptionCallback
	 */
	public function setExceptionCallback( $exceptionCallback ) {
		$this->exceptionCallback = $exceptionCallback;
	}

	/**
	 * @param string $file
	 */
	public function importFromFile( $file ) {
		$xml = file_get_contents( $file );

		if ( $xml === false ) {
			throw new RuntimeException( 'Failed to read ' . $file . '!' );
		}

		$this->importFromXML( $xml );
	}

	/**
	 * @param string $xml
	 *
	 */
	public function importFromXML( $xml ) {
		$document = new DOMDocument();

		$oldLibXmlErrors = libxml_use_internal_errors( true );
		// phpcs:ignore Generic.PHP.NoSilencedErrors -- suppress deprecation per T268847
		$oldDisable = @libxml_disable_entity_loader( true );
		$ok = $document->loadXML( $xml, LIBXML_NONET );

		if ( !$ok ) {
			$errors = libxml_get_errors();
			libxml_use_internal_errors( $oldLibXmlErrors );
			// phpcs:ignore Generic.PHP.NoSilencedErrors
			@libxml_disable_entity_loader( $oldDisable );

			foreach ( $errors as $error ) {
				/** @var LibXMLError $error */
				throw new InvalidArgumentException(
					'Malformed XML: ' . $error->message . ' in line ' . $error->line
				);
			}

			throw new InvalidArgumentException( 'Malformed XML!' );
		}

		libxml_use_internal_errors( $oldLibXmlErrors );
		// phpcs:ignore Generic.PHP.NoSilencedErrors
		@libxml_disable_entity_loader( $oldDisable );
		$sites = $this->makeSiteList( $document->documentElement );
		$this->store->saveSites( $sites );
	}

	/**
	 * @param DOMElement $root
	 *
	 * @return Site[]
	 */
	private function makeSiteList( DOMElement $root ) {
		$sites = [];

		// Old sites, to get the row IDs that correspond to the global site IDs.
		// TODO: Get rid of internal row IDs, they just get in the way. Get rid of ORMRow, too.
		$oldSites = $this->store->getSites();

		$current = $root->firstChild;
		while ( $current ) {
			if ( $current instanceof DOMElement && $current->tagName === 'site' ) {
				try {
					$site = $this->makeSite( $current );
					$key = $site->getGlobalId();

					if ( $oldSites->hasSite( $key ) ) {
						$oldSite = $oldSites->getSite( $key );
						$site->setInternalId( $oldSite->getInternalId() );
					}

					$sites[$key] = $site;
				} catch ( TimeoutException $e ) {
					throw $e;
				} catch ( Exception $ex ) {
					$this->handleException( $ex );
				}
			}

			$current = $current->nextSibling;
		}

		return $sites;
	}

	/**
	 * @param DOMElement $siteElement
	 *
	 * @return Site
	 */
	public function makeSite( DOMElement $siteElement ) {
		if ( $siteElement->tagName !== 'site' ) {
			throw new InvalidArgumentException( 'Expected <site> tag, found ' . $siteElement->tagName );
		}

		$type = $this->getAttributeValue( $siteElement, 'type', Site::TYPE_UNKNOWN );
		$site = Site::newForType( $type );

		$site->setForward( $this->hasChild( $siteElement, 'forward' ) );
		$site->setGlobalId( $this->getChildText( $siteElement, 'globalid' ) );
		$site->setGroup( $this->getChildText( $siteElement, 'group', Site::GROUP_NONE ) );
		$site->setSource( $this->getChildText( $siteElement, 'source', Site::SOURCE_LOCAL ) );

		$pathTags = $siteElement->getElementsByTagName( 'path' );
		for ( $i = 0; $i < $pathTags->length; $i++ ) {
			$pathElement = $pathTags->item( $i );
			'@phan-var DOMElement $pathElement';
			$pathType = $this->getAttributeValue( $pathElement, 'type' );
			$path = $pathElement->textContent;

			$site->setPath( $pathType, $path );
		}

		$idTags = $siteElement->getElementsByTagName( 'localid' );
		for ( $i = 0; $i < $idTags->length; $i++ ) {
			$idElement = $idTags->item( $i );
			'@phan-var DOMElement $idElement';
			$idType = $this->getAttributeValue( $idElement, 'type' );
			$id = $idElement->textContent;

			$site->addLocalId( $idType, $id );
		}

		// @todo: import <data>
		// @todo: import <config>

		return $site;
	}

	/**
	 * @param DOMElement $element
	 * @param string $name
	 * @param string|null|false $default
	 *
	 * @return null|string
	 */
	private function getAttributeValue( DOMElement $element, $name, $default = false ) {
		$node = $element->getAttributeNode( $name );

		if ( !$node ) {
			if ( $default !== false ) {
				return $default;
			} else {
				throw new RuntimeException(
					'Required ' . $name . ' attribute not found in <' . $element->tagName . '> tag'
				);
			}
		}

		return $node->textContent;
	}

	/**
	 * @param DOMElement $element
	 * @param string $name
	 * @param string|null|false $default
	 *
	 * @return null|string
	 */
	private function getChildText( DOMElement $element, $name, $default = false ) {
		$elements = $element->getElementsByTagName( $name );

		if ( $elements->length < 1 ) {
			if ( $default !== false ) {
				return $default;
			} else {
				throw new RuntimeException(
					'Required <' . $name . '> tag not found inside <' . $element->tagName . '> tag'
				);
			}
		}

		$node = $elements->item( 0 );
		return $node->textContent;
	}

	/**
	 * @param DOMElement $element
	 * @param string $name
	 *
	 * @return bool
	 */
	private function hasChild( DOMElement $element, $name ) {
		return $this->getChildText( $element, $name, null ) !== null;
	}

	private function handleException( Exception $ex ) {
		if ( $this->exceptionCallback ) {
			( $this->exceptionCallback )( $ex );
		} else {
			wfLogWarning( $ex->getMessage() );
		}
	}

}

/** @deprecated class alias since 1.42 */
class_alias( SiteImporter::class, 'SiteImporter' );
