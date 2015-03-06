<?php
namespace TYPO3\DocTools\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.DocTools".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\MethodReflection;

/**
 * TYPO3.DocTools parser for Eel helper classes.
 */
class EelHelperClassParser extends AbstractClassParser {

	/**
	 * Get the title from the Eel helper class name
	 *
	 * TODO It would be nicer to use the registered settings helper names (because these are the real names inside Eel)
	 *
	 * @return string
	 */
	protected function parseTitle() {
		$className = $this->className;
		if (preg_match('/\\\\([^\\\\]*)Helper$/', $className, $matches)) {
			return $matches[1];
		}
		return $className;
	}

	/**
	 * Iterate over all methods in the helper class
	 *
	 * TODO Check if the helper implements ProtectedContextAwareInterface and call allowsCallOfMethod for each method
	 *
	 * @return array
	 */
	protected function parseDescription() {
		$description = $this->classReflection->getDescription() . chr(10) . chr(10);

		$helperName = $this->parseTitle();

		$methods = $this->getHelperMethods();

		foreach ($methods as $methodReflection) {
			$methodDescription = $this->getMethodDescription($helperName, $methodReflection);
			$description .= $methodDescription . chr(10);
		}

		return $description;
	}

	/**
	 * @param string $helperName
	 * @param MethodReflection $methodReflection
	 * @return string
	 */
	protected function getMethodDescription($helperName, $methodReflection) {
		$methodDescription = '';
		$methodName = $methodReflection->getName();

		$methodParameters = array();
		foreach ($methodReflection->getParameters() as $parameterReflection) {
			$methodParameters[$parameterReflection->getName()] = $parameterReflection;
		}

		$parameterNames = array_keys($methodParameters);

		$methodSignature = $helperName . '.' . $methodName . '(' . implode(', ', $parameterNames) . ')';

		$methodDescription .= $methodSignature . chr(10) . str_repeat('^', strlen($methodSignature)) . chr(10) . chr(10);

		if ($methodReflection->getDescription() !== '') {
			$methodDescription .= $methodReflection->getDescription() . chr(10) . chr(10);
		}

		if ($methodReflection->isTaggedWith('param')) {
			$paramTagValues = $methodReflection->getTagValues('param');

			foreach ($paramTagValues as $paramTagValue) {
				$values = explode(' ', $paramTagValue, 3);
				list($parameterType, $parameterName) = $values;
				$parameterName = ltrim($parameterName, '$');
				$parameterDescription = isset($values[2]) ? $values[2] : '';

				$parameterOptionalSuffix = $methodParameters[$parameterName]->isOptional() ? ', *optional*' : '';

				$methodDescription .= '* ``' . $parameterName . '`` (' . $parameterType . $parameterOptionalSuffix . ') ' . $parameterDescription . chr(10);
			}

			$methodDescription .= chr(10);
		}

		if ($methodReflection->isTaggedWith('return')) {
			list($returnTagValue) = $methodReflection->getTagValues('return');

			$values = explode(' ', $returnTagValue, 2);
			list($returnType) = $values;
			$returnDescription = isset($values[1]) ? $values[1] : '';

			$methodDescription .= '**Return** (' . $returnType . ') ' . $returnDescription . chr(10);
		}
		return $methodDescription;
	}

	/**
	 * @return array<MethodReflection>
	 */
	protected function getHelperMethods() {
		$methods = $this->classReflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		$methods = array_filter($methods, function(MethodReflection $methodReflection) {
			$methodName = $methodReflection->getName();
			if (strpos($methodName, '__') === 0 || $methodName === 'allowsCallOfMethod' || $methodReflection->isTaggedWith('deprecated')) {
				return FALSE;
			}
			return TRUE;
		});
		usort($methods, function (MethodReflection $methodReflection1, MethodReflection $methodReflection2) {
			return strcmp($methodReflection1->getName(), $methodReflection2->getName());
		});
		return $methods;
	}

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\ArgumentDefinition>
	 */
	protected function parseArgumentDefinitions() {
		return array();
	}

	/**
	 * @return array<\TYPO3\DocTools\Domain\Model\CodeExample>
	 */
	protected function parseCodeExamples() {
		return array();
	}

}