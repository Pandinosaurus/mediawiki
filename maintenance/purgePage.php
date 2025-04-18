<?php
/**
 * Purges a specific page.
 *
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
 * @ingroup Maintenance
 */

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\Title\Title;

// @codeCoverageIgnoreStart
require_once __DIR__ . '/Maintenance.php';
// @codeCoverageIgnoreEnd

/**
 * Maintenance script that purges a list of pages passed through stdin
 *
 * @ingroup Maintenance
 */
class PurgePage extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Purge page.' );
		$this->addOption( 'skip-exists-check', 'Skip page existence check', false, false );
	}

	public function execute() {
		$stdin = $this->getStdin();

		while ( !feof( $stdin ) ) {
			$title = trim( fgets( $stdin ) );
			if ( $title != '' ) {
				$this->purge( $title );
			}
		}
	}

	private function purge( string $titleText ) {
		$title = Title::newFromText( $titleText );

		if ( $title === null ) {
			$this->error( 'Invalid page title' );
			return;
		}

		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );

		if ( !$this->getOption( 'skip-exists-check' ) && !$page->exists() ) {
			$this->error( "Page doesn't exist" );
			return;
		}

		if ( $page->doPurge() ) {
			$this->output( "Purged {$titleText}\n" );
		} else {
			$this->error( "Purge failed for {$titleText}" );
		}
	}
}

// @codeCoverageIgnoreStart
$maintClass = PurgePage::class;
require_once RUN_MAINTENANCE_IF_MAIN;
// @codeCoverageIgnoreEnd
