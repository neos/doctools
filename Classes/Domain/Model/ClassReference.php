<?php
namespace Neos\DocTools\Domain\Model;

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
 * @todo document
 */
class ClassReference
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array<\Neos\DocTools\Domain\Model\ArgumentDefinition>
     */
    protected $argumentDefinitions;

    /**
     * @var array<\Neos\DocTools\Domain\Model\CodeExample>
     */
    protected $codeExamples;

    /**
     * @var string
     */
    protected $deprecationNote;

    /**
     * @param string $title
     * @param string $description
     * @param array <\Neos\DocTools\Domain\Model\ArgumentDefinition> $argumentDefinitions
     * @param array <\Neos\DocTools\Domain\Model\CodeExample> $codeExamples
     * @param string $deprecationNote
     */
    public function __construct($title, $description, array $argumentDefinitions, array $codeExamples, $deprecationNote)
    {
        $this->title = $title;
        $this->description = $description;
        $this->argumentDefinitions = $argumentDefinitions;
        $this->codeExamples = $codeExamples;
        $this->deprecationNote = $deprecationNote;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array<\Neos\DocTools\Domain\Model\ArgumentDefinition>
     */
    public function getArgumentDefinitions()
    {
        return $this->argumentDefinitions;
    }

    /**
     * @return array<\Neos\DocTools\Domain\Model\CodeExample>
     */
    public function getCodeExamples()
    {
        return $this->codeExamples;
    }

    /**
     * @return string
     */
    public function getDeprecationNote()
    {
        return $this->deprecationNote;
    }
}
