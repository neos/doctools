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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\Command;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Cli\Exception\StopCommandException;
use Neos\Flow\Mvc\Exception\CommandException;
use Neos\FluidAdaptor\Exception;
use Neos\FluidAdaptor\View\StandaloneView;

/**
 * "Command Reference" command controller for the Documentation package.
 *
 * Used to create reference documentation for Flow CLI commands.
 *
 * @Flow\Scope("singleton")
 */
class CommandReferenceCommandController extends CommandController
{
    /**
     * @var array
     */
    protected array $settings;

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * Renders command reference documentation from source code.
     *
     * @param string|null $reference to render. If not specified all configured references will be rendered
     * @return void
     * @throws
     */
    public function renderCommand(string $reference = null): void
    {
        $references = $reference !== null ? [$reference] : array_keys($this->settings['commandReferences']);
        $this->renderReferences($references);
    }

    /**
     * Renders a configured collection of command reference documentation from source code.
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
        if (!isset($this->settings['collections'][$collection]['commandReferences'])) {
            $this->outputLine('Collection "%s" does not have any references', [$collection]);
            $this->quit(1);
        }
        $references = array_keys(array_filter($this->settings['collections'][$collection]['commandReferences']));
        $this->renderReferences($references);
    }

    /**
     * @throws
     */
    protected function renderReferences(array $references): void
    {
        foreach ($references as $reference) {
            $this->outputLine('Rendering Reference "%s"', [$reference]);
            $this->renderReference($reference);
        }
    }

    /**
     * @throws Exception|StopCommandException
     */
    protected function renderReference(string $reference): void
    {
        if (!isset($this->settings['commandReferences'][$reference])) {
            $this->outputLine('Command reference "%s" is not configured', [$reference]);
            $this->quit(1);
        }
        $referenceConfiguration = $this->settings['commandReferences'][$reference];
        $packageKeysToRender = array_map('strtolower', array_keys(array_filter($referenceConfiguration['packageKeys'])));

        $availableCommands = $this->commandManager->getAvailableCommands();
        $commandsByPackagesAndControllers = $this->buildCommandsIndex($availableCommands);

        $allCommandsByPackageKey = [];
        foreach ($commandsByPackagesAndControllers as $packageKey => $commandControllers) {
            if (!in_array($packageKey, $packageKeysToRender, true)) {
                $this->outputLine('Skipping package "%s"', [$packageKey]);
                continue;
            }
            $allCommands = [];
            foreach ($commandControllers as $commands) {
                foreach ($commands as $command) {
                    $argumentDescriptions = [];
                    $optionDescriptions = [];

                    foreach ($command->getArgumentDefinitions() as $commandArgumentDefinition) {
                        $argumentDescription = $commandArgumentDefinition->getDescription();
                        if ($commandArgumentDefinition->isRequired()) {
                            $argumentDescriptions[$commandArgumentDefinition->getDashedName()] = $argumentDescription;
                        } else {
                            $optionDescriptions[$commandArgumentDefinition->getDashedName()] = $argumentDescription;
                        }
                    }

                    $relatedCommands = [];
                    $relatedCommandIdentifiers = $command->getRelatedCommandIdentifiers();
                    foreach ($relatedCommandIdentifiers as $relatedCommandIdentifier) {
                        try {
                            $relatedCommand = $this->commandManager->getCommandByIdentifier($relatedCommandIdentifier);
                            $relatedCommands[$relatedCommandIdentifier] = $relatedCommand->getShortDescription();
                        } catch (CommandException) {
                            $relatedCommands[$relatedCommandIdentifier] = '*Command not available*';
                        }
                    }

                    $commandIdentifier = $command->getCommandIdentifier();
                    $allCommands[$commandIdentifier] = [
                        'identifier' => $commandIdentifier,
                        'shortDescription' => $command->getShortDescription(),
                        'description' => $this->transformMarkup($command->getDescription()),
                        'options' => $optionDescriptions,
                        'arguments' => $argumentDescriptions,
                        'relatedCommands' => $relatedCommands
                    ];
                }
            }
            ksort($allCommands);
            $allCommandsByPackageKey[strtoupper($packageKey)] = $allCommands;
        }
        ksort($allCommandsByPackageKey);

        $standaloneView = new StandaloneView();
        $templatePathAndFilename = isset($settings['templatePathAndFilename']) ? $this->settings['commandReference']['templatePathAndFilename'] : 'resource://Neos.DocTools/Private/Templates/CommandReferenceTemplate.txt';
        $standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
        $standaloneView->assign('title', $referenceConfiguration['title'] ?? $reference);
        $standaloneView->assign('allCommandsByPackageKey', $allCommandsByPackageKey);
        file_put_contents($referenceConfiguration['savePathAndFilename'], $standaloneView->render());
        $this->outputLine('DONE.');
    }

    protected function transformMarkup(string $input): string
    {
        $output = preg_replace('|<b>(((?!</b>).)*)</b>|', '**$1**', $input);
        $output = preg_replace('|<i>(((?!</i>).)*)</i>|', '*$1*', $output);
        $output = preg_replace('|<u>(((?!</u>).)*)</u>|', '*$1*', $output);
        $output = preg_replace('|<em>(((?!</em>).)*)</em>|', '*$1*', $output);
        return preg_replace('|<strike>(((?!</strike>).)*)</strike>|', '[$1]', $output);
    }

    /**
     * Builds an index of available commands. For each of them a Command object is
     * added to the commands array of this class.
     *
     * @param array<Command> $commands
     * @return array in the format ['<packageKey>' => ['<CommandControllerClassName>', ['<command1>' => $command1, '<command2>' => $command2]]]
     */
    protected function buildCommandsIndex(array $commands): array
    {
        $commandsByPackagesAndControllers = [];
        foreach ($commands as $command) {
            if ($command->isInternal()) {
                continue;
            }
            $commandIdentifier = $command->getCommandIdentifier();
            $packageKey = strstr($commandIdentifier, ':', true);
            $commandControllerClassName = $command->getControllerClassName();
            $commandName = $command->getControllerCommandName();
            $commandsByPackagesAndControllers[$packageKey][$commandControllerClassName][$commandName] = $command;
        }
        return $commandsByPackagesAndControllers;
    }
}
