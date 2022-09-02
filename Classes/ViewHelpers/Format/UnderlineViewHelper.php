<?php
declare(strict_types=1);
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

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the string, a newline character and an underline made of
 * $withCharacter as long as the original string.
 */
class UnderlineViewHelper extends AbstractViewHelper
{
    /**
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('withCharacter', 'string', 'The padding string', false, '-');
    }

    /**
     * @return string The formatted value
     */
    public function render(): string
    {
        $string = $this->renderChildren();

        return $string . chr(10) . str_repeat($this->arguments['withCharacter'], strlen($string));
    }
}
