<?php
namespace TYPO3\DocTools\Service;

/*                                                                        *
 * This script belongs to the Flow package "TYPO3.DocTools".              *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Utility\Unicode\Functions;

/**
 * Sphinx Configuration Service
 */
class SphinxConfiguration {

	/**
	 * Build the render command for rendering
	 *
	 * @param array $configuration
	 * @param string $format
	 * @return string
	 */
	public function buildRenderCommand(array $configuration, $format) {
		$overrideSettings = '';
		if (!empty($configuration['settings'])) {
			foreach ($configuration['settings'] as $setting => $value) {
				$overrideSettings .= sprintf(" -D %s=%s", $setting, escapeshellarg($value));
			}
		}

		$configurationRootPath = $configuration['configurationRootPath'];
		$localConfiguration = rtrim($configuration['documentationRootPath'], '/') . '/Settings.yml';
		if (@is_file($localConfiguration)) {
			$additionalConfiguration = $this->yamlToPython($localConfiguration);
			$configurationRootPath = $this->createTemporaryConfigurationRootPath($configurationRootPath, $additionalConfiguration);
		}

		return sprintf('sphinx-build -c %s -b %s %s %s %s 3>&1 1>&2 2>&3', escapeshellarg($configurationRootPath), $format, $overrideSettings, escapeshellarg($configuration['documentationRootPath']), escapeshellarg($configuration['renderedDocumentationRootPath']));
	}

	/**
	 * @param string $configurationRootPath
	 * @param array $additionalConfiguration
	 * @return string
	 */
	protected function createTemporaryConfigurationRootPath($configurationRootPath, array $additionalConfiguration) {
		if ($additionalConfiguration === array()) {
			return $configurationRootPath;
		}

		$buildPath = $this->generateTemporaryConfigurationRootPath($configurationRootPath);
		if (@is_dir($buildPath)) {
			Files::removeDirectoryRecursively($buildPath);
		}
		Files::copyDirectoryRecursively($configurationRootPath, $buildPath);
		$additionalConfigurationLines = implode(PHP_EOL, $additionalConfiguration);
		file_put_contents(rtrim($buildPath, '/') . '/conf.py', $additionalConfigurationLines, FILE_APPEND);

		return $buildPath;
	}

	/**
	 * @param string $configurationRootPath
	 * @return string
	 */
	protected function generateTemporaryConfigurationRootPath($configurationRootPath) {
		return FLOW_PATH_ROOT . 'Data/Temporary/Documentation/_Build/' . sha1($configurationRootPath) . '/';
	}

	/**
	 * @param array $configuration
	 */
	public function removeTemporaryConfigurationRootPath(array $configuration) {
		$configurationRootPath = $configuration['configurationRootPath'];
		$buildPath = $this->generateTemporaryConfigurationRootPath($configurationRootPath);
		if (@is_dir($buildPath)) {
			Files::removeDirectoryRecursively($buildPath);
		}
	}

	/**
	 * Converts a (simple) YAML file to Python instructions.
	 *
	 * Note: First tried to use 3rd party libraries:
	 * - spyc: http://code.google.com/p/spyc/
	 * - Symfony2 YAML: http://symfony.com/doc/current/components/yaml/introduction.html
	 * but none of them were able to parse our Settings.yml Sphinx configuration files.
	 *
	 * @param string $filename Absolute filename to Settings.yml
	 * @return string Python instruction set
	 */
	public function yamlToPython($filename) {
		$contents = file_get_contents($filename);
		$lines = explode(PHP_EOL, $contents);
		$pythonConfiguration = array();

		$i = 0;
		while ($lines[$i] !== 'conf.py:' && $i < count($lines)) {
			$i++;
		}
		while ($i < count($lines)) {
			if (preg_match('/^(\s+)([^:]+):\s*(.*)$/', $lines[$i], $matches)) {
				switch ($matches[2]) {
					case 'latex_documents':
						$pythonLine = 'latex_documents = [(' . PHP_EOL;
						if (preg_match('/^(\s+)- - /', $lines[$i + 1], $matches)) {
							$indent = $matches[1];
							$firstLine = TRUE;
							while (preg_match('/^' . $indent . '(- -|  -) (.+)$/', $lines[++$i], $matches)) {
								if (!$firstLine) {
									$pythonLine .= ',' . PHP_EOL;
								}
								$pythonLine .= sprintf('u\'%s\'', addcslashes($matches[2], "\\'"));
								$firstLine = FALSE;
							}
						}
						$pythonLine .= PHP_EOL . ')]';
						$i--;
						break;
					case 'latex_elements':
						$pythonLine = 'latex_elements = {' . PHP_EOL;
						if (preg_match('/^(\s+)/', $lines[$i + 1], $matches)) {
							$indent = $matches[1];
							$firstLine = TRUE;
							while (preg_match('/^' . $indent . '([^:]+):\s*(.*)$/', $lines[++$i], $matches)) {
								if (!$firstLine) {
									$pythonLine .= ',' . PHP_EOL;
								}
								$pythonLine .= sprintf('\'%s\': \'%s\'', $matches[1], addcslashes($matches[2], "\\'"));
								$firstLine = FALSE;
							}
						}
						$pythonLine .= PHP_EOL . '}';
						$i--;
						break;
					case 'extensions':
						$pythonLine = 'extensions = [';
						if (preg_match('/^(\s+)/', $lines[$i + 1], $matches)) {
							$indent = $matches[1];
							$firstItem = TRUE;
							while (preg_match('/^' . $indent . '- (.+)/', $lines[++$i], $matches)) {
								if (Functions::substr($matches[1], 0, 9) === 't3sphinx.') {
									// Extension t3sphinx is not compatible with JSON output
									continue;
								}

								if (!$firstItem) {
									$pythonLine .= ', ';
								}
								$pythonLine .= sprintf('\'%s\'', $matches[1]);
								$firstItem = FALSE;
							}
							$i--;
						}
						$pythonLine .= ']';
						break;
					case 'intersphinx_mapping':
						$pythonLine = 'intersphinx_mapping = {' . PHP_EOL;
						if (preg_match('/^(\s+)/', $lines[$i + 1], $matches)) {
							$indent = $matches[1];
							$firstLine = TRUE;
							while (preg_match('/^' . $indent . '(.+):/', $lines[++$i], $matches)) {
								if (!$firstLine) {
									$pythonLine .= ',' . PHP_EOL;
								}
								$pythonLine .= sprintf('\'%s\': (', $matches[1]);
								$firstItem = TRUE;
								while (preg_match('/^' . $indent . '- (.+)/', $lines[++$i], $matches)) {
									if (!$firstItem) {
										$pythonLine .= ', ';
									}
									if ($matches[1] === 'null') {
										$pythonLine .= 'None';
									} else {
										$pythonLine .= sprintf('\'%s\'', $matches[1]);
									}
									$firstItem = FALSE;
								}
								$pythonLine .= ')';
								$firstLine = FALSE;
								$i--;
							}
						}
						$pythonLine .= PHP_EOL . '}';
						$i--;
						break;
					default:
						$pythonLine = sprintf('%s = u\'%s\'', $matches[2], addcslashes($matches[3], "\\'"));
						break;
				}
				if (!empty($pythonLine)) {
					$pythonConfiguration[] = $pythonLine;
				}
			}
			$i++;
		}

		return $pythonConfiguration;
	}

}