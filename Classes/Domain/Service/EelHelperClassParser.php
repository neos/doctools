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

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\MethodReflection;

/**
 * Neos.DocTools parser for Eel helper classes.
 */
class EelHelperClassParser extends AbstractClassParser
{
    /**
     * @Flow\InjectConfiguration(package="Neos.Fusion", path="defaultContext")
     */
    protected array $defaultContextSettings = [];

    protected function parseTitle(): string
    {
        if (($registeredName = array_search($this->className, $this->defaultContextSettings, true)) !== false) {
            return $registeredName;
        }

        if (preg_match('/\\\\([^\\\\]*)Helper$/', $this->className, $matches)) {
            return $matches[1];
        }

        return $this->className;
    }

    protected function parseDescription(): string
    {
        $description = $this->classReflection->getDescription() . chr(10) . chr(10);

        $description .= 'Implemented in: ``' . $this->className . '``' . chr(10) . chr(10);

        $helperName = $this->parseTitle();
        $helperInstance = new $this->className();

        $methods = $this->getHelperMethods();
        foreach ($methods as $methodReflection) {
            if (!$helperInstance instanceof ProtectedContextAwareInterface || $helperInstance->allowsCallOfMethod($methodReflection->getName())) {
                $methodDescription = $this->getMethodDescription($helperName, $methodReflection);
                $description .= trim($methodDescription) . chr(10) . chr(10);
            }
        }

        return $description;
    }

    protected function getMethodDescription(string $helperName, MethodReflection $methodReflection): string
    {
        $methodDescription = '';
        $methodName = $methodReflection->getName();

        $methodParameters = [];
        foreach ($methodReflection->getParameters() as $parameterReflection) {
            $methodParameters[$parameterReflection->getName()] = $parameterReflection;
        }

        $parameterNames = array_keys($methodParameters);

        $methodSignature = str_replace('_', '\\_', $helperName . '.' . $methodName . '(' . implode(', ', $parameterNames) . ')');

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

        if ($methodReflection->isTaggedWith('return')) {
            [$returnTagValue] = $methodReflection->getTagValues('return');

            $values = explode(' ', $returnTagValue, 2);
            [$returnType] = $values;
            $returnDescription = $values[1] ?? '';

            $methodDescription .= '**Return** (' . $returnType . ') ' . $returnDescription . chr(10);
        }

        return $methodDescription;
    }

    /**
     * @return MethodReflection[]
     */
    protected function getHelperMethods(): array
    {
        $methods = $this->classReflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methods = array_filter($methods, static function (MethodReflection $methodReflection) {
            $methodName = $methodReflection->getName();
            return !($methodName === 'allowsCallOfMethod' || str_starts_with($methodName, '__') || $methodReflection->isTaggedWith('deprecated'));
        });
        usort($methods, static function (MethodReflection $methodReflection1, MethodReflection $methodReflection2) {
            return strcmp($methodReflection1->getName(), $methodReflection2->getName());
        });

        return $methods;
    }
}
