<?php
namespace Neos\DocTools\Command;

/*
 * This file is part of the Neos.DocTools package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\DocTools\Domain\Model\ClassReference;
use Neos\Flow\Annotations as Flow;

/**
 * Reference command controller for the Documentation package.
 *
 * Used to create reference documentation for special classes (e.g. Fluid ViewHelpers, Flow Validators, ...)
 *
 * @Flow\Scope("singleton")
 */
class ReferenceCommandController extends \Neos\Flow\Cli\CommandController
{
    /**
     * @var \Neos\Flow\Reflection\ReflectionService
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
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Renders reference documentation from source code.
     *
     * @param string $reference to render. If not specified all configured references will be rendered
     * @return void
     */
    public function renderCommand($reference = null)
    {
        $references = $reference !== null ? [$reference] : array_keys($this->settings['references']);
        $this->renderReferences($references);
    }

    /**
     * Renders a configured collection of reference documentation from source code.
     *
     * @param string $collection to render (typically the name of a package).
     * @return void
     */
    public function renderCollectionCommand($collection)
    {
        if (!isset($this->settings['collections'][$collection])) {
            $this->outputLine('Collection "%s" is not configured', [$collection]);
            $this->quit(1);
        }
        if (!isset($this->settings['collections'][$collection]['references'])) {
            $this->outputLine('Collection "%s" does not have any references', [$collection]);
            $this->quit(1);
        }
        $references = $this->settings['collections'][$collection]['references'];
        $this->renderReferences($references);
    }

    /**
     * Render a set of references to reStructuredText.
     *
     * @param array $references to render.
     * @return void
     */
    protected function renderReferences($references)
    {
        foreach ($references as $reference) {
            $this->outputLine('Rendering Reference "%s"', [$reference]);
            $this->renderReference($reference);
        }
    }

    /**
     * Render a reference to reStructuredText.
     *
     * @param string $reference
     * @return void
     */
    protected function renderReference($reference)
    {
        if (!isset($this->settings['references'][$reference])) {
            $this->outputLine('Reference "%s" is not configured', [$reference]);
            $this->quit(1);
        }
        $referenceConfiguration = $this->settings['references'][$reference];
        $affectedClassNames = $this->getAffectedClassNames($referenceConfiguration['affectedClasses']);
        $parserClassName = $referenceConfiguration['parser']['implementationClassName'];
        $parserOptions = isset($referenceConfiguration['parser']['options']) ? $referenceConfiguration['parser']['options'] : [];
        /** @var $classParser \Neos\DocTools\Domain\Service\AbstractClassParser */
        $classParser = new $parserClassName($parserOptions);
        $classReferences = [];
        foreach ($affectedClassNames as $className) {
            $classReferences[$className] = $classParser->parse($className);
        }
        usort($classReferences, function (ClassReference $a, ClassReference $b) {
            if ($a->getTitle() == $b->getTitle()) {
                return 0;
            }

            return ($a->getTitle() < $b->getTitle()) ? -1 : 1;
        });
        $standaloneView = new \Neos\FluidAdaptor\View\StandaloneView();
        $templatePathAndFilename = isset($referenceConfiguration['templatePathAndFilename']) ? $referenceConfiguration['templatePathAndFilename'] : 'resource://Neos.DocTools/Private/Templates/ClassReferenceTemplate.txt';
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
    protected function getAffectedClassNames(array $classesSelector)
    {
        if (isset($classesSelector['parentClassName'])) {
            $affectedClassNames = $this->reflectionService->getAllSubClassNamesForClass($classesSelector['parentClassName']);
        } elseif (isset($classesSelector['interface'])) {
            $affectedClassNames = $this->reflectionService->getAllImplementationClassNamesForInterface($classesSelector['interface']);
        } elseif (isset($classesSelector['classesContainingMethodsAnnotatedWith'])) {
            $affectedClassNames = $this->reflectionService->getClassesContainingMethodsAnnotatedWith($classesSelector['classesContainingMethodsAnnotatedWith']);
        } else {
            $affectedClassNames = $this->reflectionService->getAllClassNames();
        }

        foreach ($affectedClassNames as $index => $className) {
            if ($this->reflectionService->isClassAbstract($className) && (!isset($classesSelector['includeAbstractClasses']) || $classesSelector['includeAbstractClasses'] === false)) {
                unset($affectedClassNames[$index]);
            } elseif (isset($classesSelector['classNamePattern']) && preg_match($classesSelector['classNamePattern'], $className) === 0) {
                unset($affectedClassNames[$index]);
            }
        }

        return $affectedClassNames;
    }
}
