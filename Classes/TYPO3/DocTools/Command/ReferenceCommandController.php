<?php
namespace TYPO3\DocTools\Command;

/*                                                                        *
 * This script belongs to the Flow package "TYPO3.DocTools".              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\DocTools\Domain\Model\ClassReference;
use TYPO3\Flow\Annotations as Flow;

/**
 * Reference command controller for the Documentation package.
 *
 * Used to create reference documentation for special classes (e.g. Fluid ViewHelpers, Flow Validators, ...)
 *
 * @Flow\Scope("singleton")
 */
class ReferenceCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Renders reference documentation from source code.
	 *
	 * @param string $reference to render. If not specified all configured references will be rendered
	 * @return void
	 */
	public function renderCommand($reference = NULL) {
		$references = $reference !== NULL ? array($reference) : array_keys($this->settings['references']);
		foreach ($references as $reference) {
			$this->outputLine('Rendering Reference "%s"', array($reference));
			$this->renderReference($reference);
		}
	}

	/**
	 * Render a reference to reStructuredText.
	 *
	 * @param string $reference
	 * @return void
	 */
	protected function renderReference($reference) {
		if (!isset($this->settings['references'][$reference])) {
			$this->outputLine('Reference "%s" is not configured', array($reference));
			$this->quit(1);
		}
		$referenceConfiguration = $this->settings['references'][$reference];
		$affectedClassNames = $this->getAffectedClassNames($referenceConfiguration['affectedClasses']);
		$parserClassName = $referenceConfiguration['parser']['implementationClassName'];
		$parserOptions = isset($referenceConfiguration['parser']['options']) ? $referenceConfiguration['parser']['options'] : array();
		/** @var $classParser \TYPO3\DocTools\Domain\Service\AbstractClassParser */
		$classParser = new $parserClassName($parserOptions);
		$classReferences = array();
		foreach ($affectedClassNames as $className) {
			$classReferences[$className] = $classParser->parse($className);
		}
		usort($classReferences, function (ClassReference $a, ClassReference $b) {
			if ($a->getTitle() == $b->getTitle()) {
				return 0;
			}

			return ($a->getTitle() < $b->getTitle()) ? -1 : 1;
		});
		$standaloneView = new \TYPO3\Fluid\View\StandaloneView();
		$templatePathAndFilename = isset($referenceConfiguration['templatePathAndFilename']) ? $referenceConfiguration['templatePathAndFilename'] : 'resource://TYPO3.DocTools/Private/Templates/ClassReferenceTemplate.txt';
		$standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
		$standaloneView->assign('title', isset($referenceConfiguration['title']) ? $referenceConfiguration['title'] : $reference);
		$standaloneView->assign('classReferences', $classReferences);
		file_put_contents($referenceConfiguration['savePathAndFilename'], $standaloneView->render());
		$this->outputLine('DONE.');
	}

	/**
	 * @param array $classesSelector
	 * @return array
	 */
	protected function getAffectedClassNames(array $classesSelector) {
		if (isset($classesSelector['parentClassName'])) {
			$affectedClassNames = $this->reflectionService->getAllSubClassNamesForClass($classesSelector['parentClassName']);
		} elseif (isset($classesSelector['interface'])) {
			$affectedClassNames = $this->reflectionService->getAllImplementationClassNamesForInterface($classesSelector['interface']);
		} else {
			$affectedClassNames = $this->reflectionService->getAllClassNames();
		}

		foreach ($affectedClassNames as $index => $className) {
			if ($this->reflectionService->isClassAbstract($className)) {
				unset($affectedClassNames[$index]);
			} elseif (isset($classesSelector['classNamePattern']) && preg_match($classesSelector['classNamePattern'], $className) === 0) {
				unset($affectedClassNames[$index]);
			}
		}
		return $affectedClassNames;
	}
}
