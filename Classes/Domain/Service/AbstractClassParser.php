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
 * Abstract Neos.DocTools parser for classes. Extended by target specific
 * parsers to generate reference documentation.
 */
abstract class AbstractClassParser
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var \Neos\Flow\Reflection\ClassReflection
     */
    protected $classReflection;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param string $className
     * @return \Neos\DocTools\Domain\Model\ClassReference
     */
    final public function parse($className)
    {
        $this->className = $className;
        $this->classReflection = new \Neos\Flow\Reflection\ClassReflection($this->className);

        return new \Neos\DocTools\Domain\Model\ClassReference($this->parseTitle(), $this->parseDescription(), $this->parseArgumentDefinitions(), $this->parseCodeExamples(), $this->parseDeprecationNote());
    }

    /**
     * @return string
     */
    abstract protected function parseTitle();

    /**
     * @return string
     */
    abstract protected function parseDescription();

    /**
     * @return array<\Neos\DocTools\Domain\Model\ArgumentDefinition>
     */
    abstract protected function parseArgumentDefinitions();

    /**
     * @return array<\Neos\DocTools\Domain\Model\CodeExample>
     */
    abstract protected function parseCodeExamples();

    /**
     * @return string
     */
    protected function parseDeprecationNote()
    {
        if ($this->classReflection->isTaggedWith('deprecated')) {
            return implode(', ', $this->classReflection->getTagValues('deprecated'));
        } else {
            return '';
        }
    }
}
