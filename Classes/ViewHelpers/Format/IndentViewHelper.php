<?php
namespace Neos\DocTools\ViewHelpers\Format;

/*
 * This file is part of the Neos.DocTools package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Renders it's children and replaces every newline by a combination of
 * newline and $indent.
 */
class IndentViewHelper extends \Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param string $indent String used to indent
     * @param boolean $inline If TRUE, the first line will not be indented
     * @return string The formatted value
     */
    public function render($indent = "\t", $inline = false)
    {
        $string = $this->renderChildren();

        return ($inline === false ? $indent : '') . str_replace("\n", "\n" . $indent, $string);
    }
}
