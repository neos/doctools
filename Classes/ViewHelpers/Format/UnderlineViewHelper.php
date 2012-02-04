<?php
namespace Documentation\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the FLOW3 package "Documentation".              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * @todo document
 */
class UnderlineViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param string $withCharacter The padding string
	 * @return string The formatted value
	 */
	public function render($withCharacter = '-') {
		$string = $this->renderChildren();
		return $string . chr(10) . str_repeat($withCharacter, strlen($string));
	}
}
?>