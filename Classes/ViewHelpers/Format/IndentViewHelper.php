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
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('indent', 'string', 'String used to indent', false, "\t");
        $this->registerArgument('inline', 'boolean', 'If true, the first line will not be indented', false, false);
    }

    /**
     * @return string The formatted value
     */
    public function render()
    {
        $string = $this->renderChildren();

        return ($this->arguments['inline'] === false ? $this->arguments['indent'] : '') . str_replace("\n", "\n" . $this->arguments['indent'], $string);
    }
}
