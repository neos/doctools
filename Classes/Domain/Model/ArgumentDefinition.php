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
 * Describes an argument to a method, annotation, ViewHelper, …
 */
class ArgumentDefinition
{
    protected string $name;

    protected string $type;

    protected string $description;

    protected bool $required = false;

    protected mixed $defaultValue;

    public function __construct(string $name, string $type, string $description, bool $required, mixed $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }
}
