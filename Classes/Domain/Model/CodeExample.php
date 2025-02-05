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
 * @todo document
 */
class CodeExample
{
    protected string $title;

    protected string $code;

    /** @var string Example code format (xml, php, ...) */
    protected string $codeLanguage;

    protected string $output;

    public function __construct(string $title, string $code, string $codeLanguage, string $output)
    {
        $this->title = $title;
        $this->code = $code;
        $this->codeLanguage = $codeLanguage;
        $this->output = $output;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCodeLanguage(): string
    {
        return $this->codeLanguage;
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}
