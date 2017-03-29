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

use Neos\Flow\Annotations as Flow;

/**
 * "Command Reference" command controller for the Documentation package.
 *
 * Used to create reference documentation for Flow CLI commands.
 *
 * @Flow\Scope("singleton")
 */
class CommandReferenceCommandController extends \Neos\Flow\Command\HelpCommandController
{
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
     * Display help for a command
	 *
     * The help command displays help for a given command:
     * ./flow help <commandIdentifier>
     *
     * @param string $commandIdentifier Identifier of a command for more details
     * @return void
     * @Flow\Internal
     */
    public function helpCommand($commandIdentifier = null)
    {
    }

    /**
     * Renders command reference documentation from source code.
     *
     * @param string $reference to render. If not specified all configured references will be rendered
     * @return void
     */
    public function renderCommand($reference = null)
    {
        $references = $reference !== null ? [$reference] : array_keys($this->settings['commandReferences']);
        $this->renderReferences($references);
    }

    /**
     * Renders a configured collection of command reference documentation from source code.
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
        if (!isset($this->settings['collections'][$collection]['commandReferences'])) {
            $this->outputLine('Collection "%s" does not have any references', [$collection]);
            $this->quit(1);
        }
        $references = $this->settings['collections'][$collection]['commandReferences'];
        $this->renderReferences($references);
    }

    /**
     * Render a set of CLI command references to reStructuredText.
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
     * Render a CLI command reference to reStructuredText.
     *
     * @param string $reference
     * @return void
     */
    protected function renderReference($reference)
    {
        if (!isset($this->settings['commandReferences'][$reference])) {
            $this->outputLine('Command reference "%s" is not configured', [$reference]);
            $this->quit(1);
        }
        $referenceConfiguration = $this->settings['commandReferences'][$reference];
        $packageKeysToRender = $referenceConfiguration['packageKeys'];
        array_walk($packageKeysToRender, function (&$packageKey) {
            $packageKey = strtolower($packageKey);
        });

        $availableCommands = $this->commandManager->getAvailableCommands();
        $commandsByPackagesAndControllers = $this->buildCommandsIndex($availableCommands);

        $allCommandsByPackageKey = [];
        foreach ($commandsByPackagesAndControllers as $packageKey => $commandControllers) {
            if (!in_array($packageKey, $packageKeysToRender)) {
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
                        } catch (\Neos\Flow\Mvc\Exception\CommandException $exception) {
                            $relatedCommands[$relatedCommandIdentifier] = '*Command not available*';
                        }
                    }

                    $allCommands[$command->getCommandIdentifier()] = [
                        'identifier' => $command->getCommandIdentifier(),
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

        $standaloneView = new \Neos\FluidAdaptor\View\StandaloneView();
        $templatePathAndFilename = isset($settings['templatePathAndFilename']) ? $this->settings['commandReference']['templatePathAndFilename'] : 'resource://Neos.DocTools/Private/Templates/CommandReferenceTemplate.txt';
        $standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
        $standaloneView->assign('title', isset($referenceConfiguration['title']) ? $referenceConfiguration['title'] : $reference);
        $standaloneView->assign('allCommandsByPackageKey', $allCommandsByPackageKey);
        file_put_contents($referenceConfiguration['savePathAndFilename'], $standaloneView->render());
        $this->outputLine('DONE.');
    }

    /**
     * @param string $input
     * @return string
     */
    protected function transformMarkup($input)
    {
        $output = preg_replace('|\<b>(((?!\</b>).)*)\</b>|', '**$1**', $input);
        $output = preg_replace('|\<i>(((?!\</i>).)*)\</i>|', '*$1*', $output);
        $output = preg_replace('|\<u>(((?!\</u>).)*)\</u>|', '*$1*', $output);
        $output = preg_replace('|\<em>(((?!\</em>).)*)\</em>|', '*$1*', $output);
        $output = preg_replace('|\<strike>(((?!\</strike>).)*)\</strike>|', '[$1]', $output);

        return $output;
    }
}
