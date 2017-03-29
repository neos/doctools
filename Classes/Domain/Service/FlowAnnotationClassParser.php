<?php
namespace Neos\DocTools\Domain\Service;

/*                                                                        *
 * This script belongs to the Flow package "TYPO3.DocTools".              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Neos\DocTools\Domain\Model\ArgumentDefinition;
use Neos\Flow\Annotations as Flow;

/**
 * Neos.DocTools parser for Flow Annotation classes.
 */
class FlowAnnotationClassParser extends AbstractClassParser {

	/**
	 * @Flow\Inject
	 * @var \Neos\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @return string
	 */
	protected function parseTitle() {
		return substr($this->className, strrpos($this->className, '\\') + 1);
	}

	/**
	 * @return string
	 */
	protected function parseDescription() {
		$description = $this->classReflection->getDescription();
		$matches = array();
		preg_match('/@Target\(["{](.*)["}]\)$/m', $this->classReflection->getDocComment(), $matches);
		if (isset($matches[1])) {
			$targets = strtr($matches[1], array('"' => ''));
			$description .= chr(10) . chr(10) . ':Applicable to: ' . ucwords(strtolower($targets)) . chr(10);
		}

		return $description;
	}

	/**
	 * @return array<\Neos\DocTools\Domain\Model\ArgumentDefinition>
	 */
	protected function parseArgumentDefinitions() {
		$options = array();
		$classDefaultProperties = $this->classReflection->getDefaultProperties();
		$classProperties = $this->classReflection->getProperties();
		foreach ($classProperties as $propertyReflection) {
			$varTags = $propertyReflection->getTagValues('var');
			$options[] = new ArgumentDefinition($propertyReflection->getName(), array_shift($varTags), $propertyReflection->getDescription(), TRUE, $classDefaultProperties[$propertyReflection->getName()]);
		}

		return $options;
	}

	/**
	 * @return array<\Neos\DocTools\Domain\Model\CodeExample>
	 */
	protected function parseCodeExamples() {
		return array();
	}
}
