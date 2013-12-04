<?php
namespace TYPO3\DocTools\ViewHelpers\Format;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.DocTools".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Renders it's children and replaces every newline by a combination of
 * newline and $indent.
 */
class IndentViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param string $indent String used to indent
	 * @param boolean $inline If TRUE, the first line will not be indented
	 * @return string The formatted value
	 */
	public function render($indent = "\t", $inline = FALSE) {
		$string = $this->renderChildren();
		return ($inline === FALSE ? $indent : '') . str_replace("\n", "\n" . $indent, $string);
	}
}
?>