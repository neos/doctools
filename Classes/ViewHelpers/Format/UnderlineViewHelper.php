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
 * Returns the string, a newline character and an underline made of
 * $withCharacter as long as the original string.
 */
class UnderlineViewHelper extends \Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param string $withCharacter The padding string
     * @return string The formatted value
     */
    public function render($withCharacter = '-')
    {
        $string = $this->renderChildren();

        return $string . chr(10) . str_repeat($withCharacter, strlen($string));
    }
}
