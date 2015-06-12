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

use TYPO3\Flow\Annotations as Flow;

/**
 * "Command Reference" command controller for the Documentation package.
 *
 * Used to create reference documentation for TYPO3 Flow CLI commands.
 *
 * @Flow\Scope("singleton")
 */
class CommandReferenceCommandController extends \TYPO3\Flow\Command\HelpCommandController {

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
	 * Display help for a command
	 *
	 * The help command displays help for a given command:
	 * ./flow3 help <commandIdentifier>
	 *
	 * @param string $commandIdentifier Identifier of a command for more details
	 * @return void
	 * @Flow\Internal
	 */
	public function helpCommand($commandIdentifier = NULL) {}

	/**
	 * Renders command reference documentation from source code.
	 *
	 * @param string $reference to render. If not specified all configured references will be rendered
	 * @return void
	 */
	public function renderCommand($reference = NULL) {
		$references = $reference !== NULL ? array($reference) : array_keys($this->settings['commandReferences']);
		foreach ($references as $reference) {
			$this->outputLine('Rendering Reference "%s"', array($reference));
			$this->renderReference($reference);
		}
	}

	/**
	 * Render a CLI command reference to reStructuredText.
	 *
	 * @param string $reference
	 * @return void
	 */
	protected function renderReference($reference) {
		if (!isset($this->settings['commandReferences'][$reference])) {
			$this->outputLine('Command reference "%s" is not configured', array($reference));
			$this->quit(1);
		}
		$referenceConfiguration = $this->settings['commandReferences'][$reference];
		$packageKeysToRender = $referenceConfiguration['packageKeys'];
		array_walk($packageKeysToRender, function (&$packageKey) {$packageKey = strtolower($packageKey);});

		$availableCommands = $this->commandManager->getAvailableCommands();
		$commandsByPackagesAndControllers = $this->buildCommandsIndex($availableCommands);

		$allCommandsByPackageKey = array();
		foreach ($commandsByPackagesAndControllers as $packageKey => $commandControllers) {
			if (!in_array($packageKey, $packageKeysToRender)) {
				$this->outputLine('Skipping package "%s"', array($packageKey));
				continue;
			}
			$allCommands = array();
			foreach ($commandControllers as $commands) {
				foreach ($commands as $command) {

					$argumentDescriptions = array();
					$optionDescriptions = array();

					foreach ($command->getArgumentDefinitions() as $commandArgumentDefinition) {
						$argumentDescription = $commandArgumentDefinition->getDescription();
						if ($commandArgumentDefinition->isRequired()) {
							$argumentDescriptions[$commandArgumentDefinition->getDashedName()] = $argumentDescription;
						} else {
							$optionDescriptions[$commandArgumentDefinition->getDashedName()] = $argumentDescription;
						}
					}

					$relatedCommands = array();
					$relatedCommandIdentifiers = $command->getRelatedCommandIdentifiers();
					foreach ($relatedCommandIdentifiers as $relatedCommandIdentifier) {
						try {
							$relatedCommand = $this->commandManager->getCommandByIdentifier($relatedCommandIdentifier);
							$relatedCommands[$relatedCommandIdentifier] = $relatedCommand->getShortDescription();
						} catch (\TYPO3\Flow\Mvc\Exception\CommandException $exception) {
							$relatedCommands[$relatedCommandIdentifier] = '*Command not available*';
						}
					}

					$allCommands[$command->getCommandIdentifier()] = array(
						'identifier' => $command->getCommandIdentifier(),
						'shortDescription' => $command->getShortDescription(),
						'description' => $this->transformMarkup($command->getDescription()),
						'options' => $optionDescriptions,
						'arguments' => $argumentDescriptions,
						'relatedCommands' => $relatedCommands
					);
				}
			}
			ksort($allCommands);
			$allCommandsByPackageKey[strtoupper($packageKey)] = $allCommands;
		}
		ksort($allCommandsByPackageKey);

		$standaloneView = new \TYPO3\Fluid\View\StandaloneView();
		$templatePathAndFilename = isset($settings['templatePathAndFilename']) ? $this->settings['commandReference']['templatePathAndFilename'] : 'resource://TYPO3.DocTools/Private/Templates/CommandReferenceTemplate.txt';
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
	protected function transformMarkup($input) {
		$output =  preg_replace('|\<b>(((?!\</b>).)*)\</b>|', '**$1**', $input);
		$output =  preg_replace('|\<i>(((?!\</i>).)*)\</i>|', '*$1*', $output);
		$output =  preg_replace('|\<u>(((?!\</u>).)*)\</u>|', '*$1*', $output);
		$output =  preg_replace('|\<em>(((?!\</em>).)*)\</em>|', '*$1*', $output);
		$output =  preg_replace('|\<strike>(((?!\</strike>).)*)\</strike>|', '[$1]', $output);
		return $output;
	}
}
