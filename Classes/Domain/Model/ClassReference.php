<?php
declare(strict_types=1);
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
 * Describes a class with it's description parsed from the docblock and reflection information
 *
 * Depending on the class this can be tailored to Eel helpers, ViewHelpers, type converters, …
 */
class ClassReference
{
    protected string $title;

    protected string $description;

    /** @var ArgumentDefinition[] */
    protected array $argumentDefinitions;

    /** @var CodeExample[] */
    protected array $codeExamples;

    protected string $deprecationNote;

    public function __construct(string $title, string $description, array $argumentDefinitions, array $codeExamples, string $deprecationNote)
    {
        $this->title = $title;
        $this->description = $description;
        $this->argumentDefinitions = $argumentDefinitions;
        $this->codeExamples = $codeExamples;
        $this->deprecationNote = $deprecationNote;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getArgumentDefinitions(): array
    {
        return $this->argumentDefinitions;
    }

    public function getCodeExamples(): array
    {
        return $this->codeExamples;
    }

    public function getDeprecationNote(): string
    {
        return $this->deprecationNote;
    }
}
