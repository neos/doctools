<?php
declare(strict_types=1);
namespace Neos\DocTools\Domain\Service;

/*
 * This file is part of the Neos.DocTools package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\DocTools\Domain\Model\ArgumentDefinition;
use Neos\DocTools\Domain\Model\CodeExample;

/**
 * Neos.DocTools parser for Flow Validator classes.
 */
class FlowValidatorClassParser extends AbstractClassParser
{
    protected function parseTitle(): string
    {
        return substr($this->className, strrpos($this->className, '\\') + 1);
    }

    protected function parseDescription(): string
    {
        $description = $this->classReflection->getDescription();

        $methodReflection = $this->classReflection->getMethod('isValid');
        $description .= chr(10) . chr(10) . $methodReflection->getDescription();

        $classDefaultProperties = $this->classReflection->getDefaultProperties();
        if ($classDefaultProperties['acceptsEmptyValues'] === true) {
            $description .= chr(10) . chr(10) . '.. note:: A value of NULL or an empty string (\'\') is considered valid';
        }

        return $description;
    }

    /**
     * @return ArgumentDefinition[]
     */
    protected function parseArgumentDefinitions(): array
    {
        $options = [];
        $classDefaultProperties = $this->classReflection->getDefaultProperties();
        foreach ($classDefaultProperties['supportedOptions'] as $optionName => $optionData) {
            $options[] = new ArgumentDefinition($optionName, $optionData[2], $optionData[1], isset($optionData[3]), $optionData[1]);
        }

        return $options;
    }

    /**
     * @return CodeExample[]
     */
    protected function parseCodeExamples(): array
    {
        return [];
    }
}
