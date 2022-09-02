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

    protected string $codeFormat;

    protected string $output;

    /**
     * Constructor for this code example
     *
     * @param string $title Title of the example
     * @param string $code Example code
     * @param string $codeFormat Example code format (xml, php, ...)
     * @param string $output Expected output of the code example
     */
    public function __construct(string $title, string $code, string $codeFormat, string $output)
    {
        $this->title = $title;
        $this->code = $code;
        $this->codeFormat = $codeFormat;
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

    public function getCodeFormat(): string
    {
        return $this->codeFormat;
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}
