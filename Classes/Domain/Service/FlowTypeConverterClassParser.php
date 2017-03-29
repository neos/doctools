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

/**
 * Neos.DocTools parser for Flow TypeConverter classes.
 */
class FlowTypeConverterClassParser extends AbstractClassParser
{
    /**
     * @return string
     */
    protected function parseTitle()
    {
        return substr($this->className, strrpos($this->className, '\\') + 1);
    }

    /**
     * @return string
     */
    protected function parseDescription()
    {
        $description = $this->classReflection->getDescription();

        $classDefaultProperties = $this->classReflection->getDefaultProperties();

        $description .= chr(10) . chr(10) . ':Priority: ' . $classDefaultProperties['priority'] . chr(10);
        $description .= ':Target type: ' . $classDefaultProperties['targetType'] . chr(10);
        if (count($classDefaultProperties['sourceTypes']) === 1) {
            $description .= ':Source type: ' . current($classDefaultProperties['sourceTypes']) . chr(10);
        } else {
            $description .= ':Source types:' . chr(10);
            $description .= ' * ' . implode(chr(10) . ' * ', $classDefaultProperties['sourceTypes']);
        }

        return $description;
    }

    /**
     * @return array<\Neos\DocTools\Domain\Model\ArgumentDefinition>
     */
    protected function parseArgumentDefinitions()
    {
        return [];
    }

    /**
     * @return array<\Neos\DocTools\Domain\Model\CodeExample>
     */
    protected function parseCodeExamples()
    {
        return [];
    }
}
