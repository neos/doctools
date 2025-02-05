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

/**
 * Neos.DocTools parser for Flow Annotation classes.
 */
class FlowAnnotationClassParser extends AbstractClassParser
{
    protected function parseTitle(): string
    {
        return substr($this->className, strrpos($this->className, '\\') + 1);
    }

    protected function parseDescription(): string
    {
        $description = $this->classReflection->getDescription();
        $matches = [];
        preg_match('/@Target\(["{](.*)["}]\)$/m', $this->classReflection->getDocComment(), $matches);
        if (isset($matches[1])) {
            $targets = strtr($matches[1], ['"' => '']);
            $description .= chr(10) . chr(10) . ':Applicable to: ' . ucwords(strtolower($targets)) . chr(10);
        }

        return $description;
    }

    protected function parseArgumentDefinitions(): array
    {
        $options = [];
        $classDefaultProperties = $this->classReflection->getDefaultProperties();
        $classProperties = $this->classReflection->getProperties();
        foreach ($classProperties as $propertyReflection) {
            $varTags = $propertyReflection->getTagValues('var');
            $options[] = new ArgumentDefinition($propertyReflection->getName(), array_shift($varTags), $propertyReflection->getDescription(), true, $classDefaultProperties[$propertyReflection->getName()]);
        }

        return $options;
    }
}
