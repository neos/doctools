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
 * Renders it's children and replaces every newline by a combination of
 * newline and $indent.
 */
class IndentViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('indent', 'string', 'String used to indent', false, "\t");
        $this->registerArgument('inline', 'boolean', 'If true, the first line will not be indented', false, false);
    }

    public function render(): string
    {
        $string = $this->renderChildren();

        return ($this->arguments['inline'] === false ? $this->arguments['indent'] : '') . str_replace("\n", "\n" . $this->arguments['indent'], $string);
    }
}
