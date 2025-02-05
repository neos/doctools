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
use Neos\DocTools\Domain\Model\CodeExample;

/**
 * Neos.DocTools parser for Fluid ViewHelper classes.
 */
class FluidViewHelperClassParser extends AbstractClassParser
{
    private const PATTERN_CODE_EXAMPLES = '/<code title="(?P<title>[^"]+)">\n(?P<code>.*?)<\/code>\n\s*<output>\n(?P<output>.*?)<\/output>/s';
    private const PATTERN_DESCRIPTION = '/(?P<description>.*)(?=\n\s=\sExamples\s=\n)/s';

    protected function parseTitle(): string
    {
        $classNameWithoutSuffix = substr($this->className, 0, -10);
        foreach ($this->options['namespaces'] as $namespaceIdentifier => $fullyQualifiedNamespace) {
            if (strpos($this->className, $fullyQualifiedNamespace) === 0) {
                $titleSegments = explode('\\', substr($classNameWithoutSuffix, strlen($fullyQualifiedNamespace) + 1));

                return sprintf('%s:%s', $namespaceIdentifier, implode('.', array_map('lcfirst', $titleSegments)));
            }
        }

        return substr($this->className, strrpos($this->className, '\\') + 1);
    }

    protected function parseDescription(): string
    {
        $description = $this->classReflection->getDescription();
        $matches = [];
        preg_match(self::PATTERN_DESCRIPTION, $description, $matches);
        $description = $matches['description'] ?? $description;

        $description .= chr(10) . chr(10) . ':Implementation: ' . str_replace('\\', '\\\\', $this->className) . chr(10);

        return $description;
    }

    protected function parseArgumentDefinitions(): array
    {
        $viewHelper = new $this->className();
        $viewHelperArguments = $viewHelper->prepareArguments();
        $argumentDefinitions = [];
        foreach ($viewHelperArguments as $viewHelperArgument) {
            $argumentDefinitions[] = new ArgumentDefinition($viewHelperArgument->getName(), $viewHelperArgument->getType(), $viewHelperArgument->getDescription(), $viewHelperArgument->isRequired(), $viewHelperArgument->getDefaultValue());
        }

        return $argumentDefinitions;
    }

    protected function parseCodeExamples(): array
    {
        $matches = [];
        preg_match_all(self::PATTERN_CODE_EXAMPLES, $this->classReflection->getDescription(), $matches, PREG_SET_ORDER);
        $examples = [];
        foreach ($matches as $match) {
            $examples[] = new CodeExample(trim($match['title']), trim($match['code']), 'xml', trim($match['output']));
        }

        return $examples;
    }
}
