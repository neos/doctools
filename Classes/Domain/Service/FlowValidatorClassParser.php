<?php
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

/**
 * Neos.DocTools parser for Flow Validator classes.
 */
class FlowValidatorClassParser extends AbstractClassParser
{
    /**
     * @return string
     */
    protected function parseTitle()
    {
        return substr($this->className, strrpos($this->className, '\\') + 1);
    }

    /**
     * @return array
     */
    protected function parseDescription()
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
     * @return array<\Neos\DocTools\Domain\Model\ArgumentDefinition>
     */
    protected function parseArgumentDefinitions()
    {
        $options = [];
        $classDefaultProperties = $this->classReflection->getDefaultProperties();
        foreach ($classDefaultProperties['supportedOptions'] as $optionName => $optionData) {
            $options[] = new ArgumentDefinition($optionName, $optionData[2], $optionData[1], isset($optionData[3]), $optionData[1]);
        }

        return $options;
    }

    /**
     * @return array<\Neos\DocTools\Domain\Model\CodeExample>
     */
    protected function parseCodeExamples()
    {
        return [];
    }
}
