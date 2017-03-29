<?php
namespace Neos\DocTools\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the Flow package "TYPO3.DocTools".              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Returns the string, a newline character and an underline made of
 * $withCharacter as long as the original string.
 */
class UnderlineViewHelper extends \Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param string $withCharacter The padding string
	 * @return string The formatted value
	 */
	public function render($withCharacter = '-') {
		$string = $this->renderChildren();
		return $string . chr(10) . str_repeat($withCharacter, strlen($string));
	}
}
