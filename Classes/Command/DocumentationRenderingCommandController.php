<?php
namespace TYPO3\DocTools\Command;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.DocTools".             *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Documentation rendering command controller
 * to be used as a basis for the documentation rendering by the doc team
 *
 * THIS CLASS IS A STUB TO BE EXTENDED BY THE DOCUMENTATION TEAM
 *
 * @FLOW3\Scope("singleton")
 */
class DocumentationRenderingCommandController extends \TYPO3\FLOW3\Cli\CommandController {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Queue\Job\JobManager
	 */
	protected $jobManager;

	/**
	 * @param string $pathToRestFiles
	 */
	public function addJobCommand($pathToRestFiles) {
		$job = new \TYPO3\DocTools\Job\DocumentationRenderJob($pathToRestFiles);

		$this->jobManager->queue('renderDocumentation', $job);
	}
}

?>