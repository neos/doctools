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
use Neos\DocTools\Domain\Model\ClassReference;
use Neos\DocTools\Domain\Model\CodeExample;
use Neos\Flow\Reflection\ClassReflection;
use Neos\Flow\Reflection\Exception\ClassLoadingForReflectionFailedException;

/**
 * Abstract Neos.DocTools parser for classes. Extended by target specific
 * parsers to generate reference documentation.
 */
abstract class AbstractClassParser
{
    protected array $options;

    protected string $className;

    protected ClassReflection $classReflection;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @throws ClassLoadingForReflectionFailedException
     */
    final public function parse(string $className): ClassReference
    {
        $this->className = $className;
        $this->classReflection = new ClassReflection($this->className);

        return new ClassReference($this->parseTitle(), $this->parseDescription(), $this->parseArgumentDefinitions(), $this->parseCodeExamples(), $this->parseDeprecationNote());
    }

    abstract protected function parseTitle(): string;

    abstract protected function parseDescription(): string;

    /**
     * @return ArgumentDefinition[]
     */
    protected function parseArgumentDefinitions(): array
    {
        return [];
    }

    /**
     * @return CodeExample[]
     */
    protected function parseCodeExamples(): array
    {
        return [];
    }

    protected function parseDeprecationNote(): string
    {
        if ($this->classReflection->isTaggedWith('deprecated')) {
            return implode(', ', $this->classReflection->getTagValues('deprecated'));
        }

        return '';
    }
}
