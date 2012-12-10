<?php
namespace TYPO3\DocTools\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.DocTools".        *
 *                                                                        *
 *                                                                        *
 */

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
	 * Render a CLI command reference to reStructuredText.
	 *
	 * @param string $packageKey The package key to limit to, all packages are considered if not given.
	 * @return void
	 */
	public function renderCommand($packageKey = NULL) {
		$exceedingArguments = $this->request->getExceedingArguments();
		if (count($exceedingArguments) > 0 && $packageKey === NULL) {
			$packageKeyToRender = strtolower($exceedingArguments[0]);
		} else {
			$packageKeyToRender = $packageKey === NULL ? NULL : strtolower($packageKey);
		}
		$availableCommands = $this->commandManager->getAvailableCommands();
		$commandsByPackagesAndControllers = $this->buildCommandsIndex($availableCommands);

		$allCommandsByPackageKey = array();
		foreach ($commandsByPackagesAndControllers as $packageKey => $commandControllers) {
			if ($packageKeyToRender !== NULL && $packageKeyToRender !== $packageKey) {
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

					$allCommands[] = array(
						'identifier' => $command->getCommandIdentifier(),
						'shortDescription' => $command->getShortDescription(),
						'description' => $this->transformMarkup($command->getDescription()),
						'options' => $optionDescriptions,
						'arguments' => $argumentDescriptions,
						'relatedCommands' => $relatedCommands
					);
				}
			}
			$allCommandsByPackageKey[strtoupper($packageKey)] = $allCommands;
		}

		$standaloneView = new \TYPO3\Fluid\View\StandaloneView();
		$templatePathAndFilename = isset($settings['templatePathAndFilename']) ? $this->settings['commandReference']['templatePathAndFilename'] : 'resource://TYPO3.DocTools/Private/Templates/CommandReferenceTemplate.txt';
		$standaloneView->setTemplatePathAndFilename($templatePathAndFilename);
		$standaloneView->assign('title', $this->settings['commandReference']['title']);
		$standaloneView->assign('allCommandsByPackageKey', $allCommandsByPackageKey);
		file_put_contents($this->settings['commandReference']['savePathAndFilename'], $standaloneView->render());
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

?>