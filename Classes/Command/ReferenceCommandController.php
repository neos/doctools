<?php
declare(strict_types=1);
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
use Neos\DocTools\Domain\Service\AbstractClassParser;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Cli\Exception\StopCommandException;
use Neos\Flow\Reflection\Exception\ClassLoadingForReflectionFailedException;
use Neos\Flow\Reflection\ReflectionService;
use Neos\FluidAdaptor\View\StandaloneView;

/**
 * Reference command controller for the Documentation package.
 *
 * Used to create reference documentation for special classes (e.g. Fluid ViewHelpers, Flow Validators, ...)
 *
 * @Flow\Scope("singleton")
 */
class ReferenceCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    protected array $settings;

    public function injectSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * Renders reference documentation from source code.
     *
     * @param string|null $reference to render. If not specified all configured references will be rendered
     * @return void
     * @throws
     */
    public function renderCommand(string $reference = null): void
    {
        $references = $reference !== null ? [$reference] : array_keys($this->settings['references']);
        $this->renderReferences($references);
    }

    /**
     * Renders a configured collection of reference documentation from source code.
     *
     * @param string $collection to render (typically the name of a package).
     * @return void
     * @throws
     */
    public function renderCollectionCommand(string $collection): void
    {
        if (!isset($this->settings['collections'][$collection])) {
            $this->outputLine('Collection "%s" is not configured', [$collection]);
            $this->quit(1);
        }
        if (!isset($this->settings['collections'][$collection]['references'])) {
            $this->outputLine('Collection "%s" does not have any references', [$collection]);
            $this->quit(1);
        }
        $references = array_keys(array_filter($this->settings['collections'][$collection]['references']));
        $this->renderReferences($references);
    }

    /**
     * @throws ClassLoadingForReflectionFailedException
     * @throws StopCommandException
     */
    protected function renderReferences(array $references): void
    {
        foreach ($references as $reference) {
            $this->outputLine('Rendering Reference "%s"', [$reference]);
            $this->renderReference($reference);
        }
    }

    /**
     * @throws ClassLoadingForReflectionFailedException
     * @throws StopCommandException
     */
    protected function renderReference(string $reference): void
    {
        if (!isset($this->settings['references'][$reference])) {
            $this->outputLine('Reference "%s" is not configured', [$reference]);
            $this->quit(1);
        }
        $referenceConfiguration = $this->settings['references'][$reference];
        $affectedClassNames = $this->getAffectedClassNames($referenceConfiguration['affectedClasses']);
        $parserClassName = $referenceConfiguration['parser']['implementationClassName'];
        $parserOptions = $referenceConfiguration['parser']['options'] ?? [];
        /** @var AbstractClassParser $classParser */
        $classParser = new $parserClassName($parserOptions);
        $classReferences = [];
        foreach ($affectedClassNames as $className) {
            $classReferences[$className] = $classParser->parse($className);
        }
        usort($classReferences, static function (ClassReference $a, ClassReference $b) {
            if ($a->getTitle() === $b->getTitle()) {
                return 0;
            }

            return ($a->getTitle() < $b->getTitle()) ? -1 : 1;
        });
        $standaloneView = new StandaloneView();
        $templatePathAndFilename = $referenceConfiguration['templatePathAndFilename'] ?? 'resource://Neos.DocTools/Private/Templates/ClassReferenceTemplate.txt';
        $standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
        $standaloneView->assign('title', $referenceConfiguration['title'] ?? $reference);
        $standaloneView->assign('classReferences', $classReferences);
        file_put_contents($referenceConfiguration['savePathAndFilename'], $standaloneView->render());
        $this->outputLine('Written to: ' . $referenceConfiguration['savePathAndFilename']);
        $this->outputLine('DONE.');
    }

    protected function getAffectedClassNames(array $classesSelector): array
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
            if ((!isset($classesSelector['includeAbstractClasses']) || $classesSelector['includeAbstractClasses'] === false) && $this->reflectionService->isClassAbstract($className)) {
                unset($affectedClassNames[$index]);
            } elseif (isset($classesSelector['classNamePattern']) && preg_match($classesSelector['classNamePattern'], $className) === 0) {
                unset($affectedClassNames[$index]);
            } elseif ($this->reflectionService->isClassAnnotatedWith($className, Flow\Internal::class)) {
                unset($affectedClassNames[$index]);
            }
        }

        return $affectedClassNames;
    }
}
