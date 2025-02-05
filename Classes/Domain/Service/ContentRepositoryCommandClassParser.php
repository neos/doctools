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

use Neos\Flow\Reflection\MethodReflection;

/**
 * Neos.DocTools parser for Content Repository Command classes.
 */
class ContentRepositoryCommandClassParser extends AbstractClassParser
{
    protected function parseTitle(): string
    {
        return substr($this->className, strrpos($this->className, '\\') + 1);
    }

    protected function parseDescription(): string
    {
        $description = trim($this->classReflection->getDescription());

        if ($this->classReflection->hasMethod('create')) {
            $methodReflection = $this->classReflection->getMethod('create');
            $methodDescription = $this->getCreateMethodDescription($methodReflection);
            $description .= chr(10) . chr(10) . trim($methodDescription);
        }

        return $description;
    }


    protected function getCreateMethodDescription(MethodReflection $methodReflection): string
    {
        $methodDescription = '';

        $methodParameters = [];
        foreach ($methodReflection->getParameters() as $parameterReflection) {
            $methodParameters[$parameterReflection->getName()] = $parameterReflection;
        }

        $parameterNames = array_keys($methodParameters);

        $methodSignature = 'create(' . implode(', ', $parameterNames) . ')';

        $methodDescription .= $methodSignature . chr(10) . str_repeat('^', strlen($methodSignature)) . chr(10) . chr(10);

        if ($methodReflection->getDescription() !== '') {
            $methodDescription .= $methodReflection->getDescription() . chr(10) . chr(10);
        }

        if ($methodReflection->isTaggedWith('param')) {
            $paramTagValues = $methodReflection->getTagValues('param');

            foreach ($paramTagValues as $paramTagValue) {
                $values = explode(' ', $paramTagValue, 3);
                [$parameterType, $parameterName] = $values;
                $parameterName = ltrim($parameterName, '$');
                $parameterDescription = $values[2] ?? '';

                $parameterOptionalSuffix = $methodParameters[$parameterName]->isOptional() ? ', *optional*' : '';

                $methodDescription .= trim('* ``' . $parameterName . '`` (' . $parameterType . $parameterOptionalSuffix . ') ' . $parameterDescription) . chr(10);
            }

            $methodDescription .= chr(10);
        }

        return $methodDescription;
    }
}
