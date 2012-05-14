<?php
namespace TYPO3\DocTools\Job;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.DocTools".             *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Job to render a single documentation.
 *
 * THIS CLASS IS A STUB TO BE EXTENDED BY THE DOCUMENTATION TEAM
 */
class DocumentationRenderJob implements \TYPO3\Queue\Job\JobInterface {

	/**
	 * @var string
	 * The path towards the ReST files to render
	 */
	protected $pathToRestFiles;

	/**
	 * @param string $pathToRestFiles
	 */
	public function __construct($pathToRestFiles) {
		$this->pathToRestFiles = $pathToRestFiles;
	}

	public function execute(\TYPO3\Queue\QueueInterface $queue, \TYPO3\Queue\Message $message) {
		echo 'Processing ' . $this->pathToRestFiles;
		sleep(5);

		echo 'TODO: implement rendering here';

		return TRUE;
	}
	public function getIdentifier() {
		return 'documentationRender';
	}
	public function getLabel() {
		return 'Documentation Render ' . $this->pathToRestFiles;
	}
}
?>