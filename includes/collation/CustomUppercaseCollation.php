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
 * @since 1.30
 *
 * @file
 */

use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageFactory;

/**
 * Resort normal UTF-8 order by putting a bunch of stuff in PUA
 *
 * This takes a bunch of characters (The alphabet) that should,
 * be together, and converts them all to private-use-area characters
 * so that they are all sorted in the right order relative to each
 * other.
 *
 * This renumbers characters starting at U+F3000 (Chosen to avoid
 * conflicts with other people using private use area)
 *
 * This does not support fancy things like secondary differences, etc.
 * (It supports digraphs, trigraphs etc. though.)
 *
 * It is expected most people will subclass this and just override the
 * constructor to hard-code an alphabet.
 */
class CustomUppercaseCollation extends NumericUppercaseCollation {

	/** @var array Sorted array of letters */
	private $alphabet;

	/** @var array List of private use area codes */
	private $puaSubset;

	/** @var array */
	private $firstLetters;

	/**
	 * @note This assumes $alphabet does not contain U+F3000-U+F3FFF
	 *
	 * @param LanguageFactory $languageFactory
	 * @param array $alphabet Sorted array of uppercase characters. Can have array elements for equal weight chars
	 * @param string|Language $digitTransformLang What language for number sorting.
	 */
	public function __construct(
		LanguageFactory $languageFactory,
		array $alphabet,
		$digitTransformLang
	) {
		if ( count( $alphabet ) < 1 || count( $alphabet ) >= 4096 ) {
			throw new UnexpectedValueException( "Alphabet must be < 4096 items" );
		}
		$digitTransformLang = $digitTransformLang instanceof Language
			? $digitTransformLang
			: $languageFactory->getLanguage( $digitTransformLang );

		$this->puaSubset = [];
		$this->alphabet = [];
		$len = count( $alphabet );
		for ( $i = 0; $i < $len; $i++ ) {
			// We allow alphabet to contain array members if multiple characters should be sorted as equivalent.
			for ( $j = 0; $j < count( (array)( $alphabet[$i] ) ); $j++ ) {
				$this->puaSubset[] = "\xF3\xB3" . chr( (int)floor( $i / 64 ) + 128 ) . chr( ( $i % 64 ) + 128 );
				// For digraphs, we uppercase it all during sorting but not when displaying first letter.
				$this->alphabet[] = $digitTransformLang->uc( ( (array)( $alphabet[$i] ) )[$j] );
				// Note: first letters is always first of group
				$this->firstLetters[] = ( (array)( $alphabet[$i] ) )[0];
			}
		}

		// Sort these arrays so that any trigraphs, digraphs etc. are first
		// (and they get replaced first in convertToPua()).
		$lengths = array_map( 'mb_strlen', $this->alphabet );
		array_multisort( $lengths, SORT_DESC, $this->firstLetters, $this->alphabet, $this->puaSubset );

		parent::__construct( $languageFactory, $digitTransformLang );
	}

	private function convertToPua( string $string ): string {
		return str_replace( $this->alphabet, $this->puaSubset, $string );
	}

	/** @inheritDoc */
	public function getSortKey( $string ) {
		return $this->convertToPua( parent::getSortKey( $string ) );
	}

	/** @inheritDoc */
	public function getFirstLetter( $string ) {
		$sortkey = $this->getSortKey( $string );

		// In case a title begins with a character from our alphabet, return the corresponding
		// first-letter. (This also happens if the title has a corresponding PUA code in it, to avoid
		// inconsistent behaviour. This class mostly assumes that people will not use PUA codes.)
		$index = array_search( substr( $sortkey, 0, 4 ), $this->puaSubset );
		if ( $index !== false ) {
			return $this->firstLetters[ $index ];
		}

		// String begins with a character outside of our alphabet, fall back
		return parent::getFirstLetter( $string );
	}
}
