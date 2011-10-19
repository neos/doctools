<?php
namespace Documentation\Command;

/*                                                                        *
 * This script belongs to the FLOW3 package "Documentation".              *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Import command controller for the Documentation package
 *
 * @FLOW3\Scope("singleton")
 */
class DocumentationCommandController extends \TYPO3\FLOW3\MVC\Controller\CommandController {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\TYPO3\Domain\Repository\SiteRepository
	 */
	protected $siteRepository;

	/**
	 * @FLOW3\Inject
	 * @var TYPO3\FLOW3\Resource\ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @FLOW3\Inject
	 * @var TYPO3\FLOW3\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var array
	 */
	protected $bundleConfiguration = array();

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Renders reST to json
	 *
	 * @param string $bundle bundle to render. If not specified all configured bundles will be rendered
	 * @return void
	 */
	public function renderCommand($bundle = NULL) {
		$bundles = $bundle !== NULL ? array($bundle) : array_keys($this->settings['bundles']);
		$defaultConfiguration = isset($this->settings['defaultConfiguration']) ? $this->settings['defaultConfiguration'] : array();
		foreach ($bundles as $bundle) {
			if (!isset($this->settings['bundles'][$bundle])) {
				$this->output('Bundle "%s" is not configured', array($bundle));
				$this->quit(1);
			}
			$configuration = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($defaultConfiguration, $this->settings['bundles'][$bundle]);
			$this->outputLine('Rendering bundle "%s"', array($bundle));
			$renderCommand = sprintf('sphinx-build -c %s -b json %s %s', $configuration['configurationRootPath'], $configuration['documentationRootPath'], $configuration['renderedDocumentationRootPath']);
			exec($renderCommand, $output, $result);
			if ($result !== 0) {
				$this->output('Could not execute sphinx-build command for Bundle %s', array($bundle));
				$this->quit(1);
			}
		}
	}

	/**
	 * Imports json to TYPO3CR nodes
	 *
	 * @param string $bundle bundle to render. If not specified all configured bundles will be rendered
	 * @param boolean $force if set, documentation will be imported even though target node exists
	 * @return void
	 */
	public function importCommand($bundle = NULL, $force = FALSE) {
		$bundles = $bundle !== NULL ? array($bundle) : array_keys($this->settings['bundles']);
		$defaultConfiguration = isset($this->settings['defaultConfiguration']) ? $this->settings['defaultConfiguration'] : array();

		foreach ($bundles as $bundle) {
			if (!isset($this->settings['bundles'][$bundle])) {
				$this->output('Bundle "%s" is not configured', array($bundle));
				$this->quit(1);
			}
			$this->bundleConfiguration = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($defaultConfiguration, $this->settings['bundles'][$bundle]);
			$this->importBundle($bundle, $force);
			$this->outputLine('---');
		}
		$this->outputLine('Done');
	}

	/**
	 * @param string $bundle
	 * @param boolean $force
	 * @return void
	 */
	protected function importBundle($bundle, $force) {
		$this->outputLine('Importing bundle "%s"', array($bundle));
		$renderedDocumentationRootPath = rtrim($this->bundleConfiguration['renderedDocumentationRootPath'], '/');

		$contentContext = new \TYPO3\TYPO3\Domain\Service\ContentContext('live');
		$contentContext->setInvisibleContentShown(TRUE);
		$siteNode = $contentContext->getCurrentSiteNode();
		$importRootNode = $siteNode->getNode($this->bundleConfiguration['importRootNodePath']);
		if ($importRootNode === NULL) {
			$this->output('ImportRootNode "%s" does not exist!', array($this->bundleConfiguration['importRootNodePath']));
			$this->quit(1);
		}

		$jsonFileNames = \TYPO3\FLOW3\Utility\Files::readDirectoryRecursively($renderedDocumentationRootPath, '.fjson');
		if ($jsonFileNames === array()) {
			$this->output('The folder "%s" contains no fjson files. Did you render the documentation?', array($renderedDocumentationRootPath));
			$this->quit(1);
		}
		foreach ($jsonFileNames as $jsonFileName) {
			$data = json_decode(file_get_contents($jsonFileName));
			if (!isset($data->body)) {
				continue;
			}
			$relativeNodePath = substr($jsonFileName, strlen($renderedDocumentationRootPath) + 1, -6);
			$relativeNodePath = $this->normalizeNodePath($relativeNodePath);
			$segments = explode('/', $relativeNodePath);
			$pageNode = $importRootNode;
			while ($segment = array_shift($segments)) {
				$nodeName = preg_replace('/[^a-z0-9\-]/', '', $segment);
				$subPageNode = $pageNode->getNode($nodeName);
				if ($subPageNode === NULL) {
					$this->outputLine('Creating page node "%s"', array($relativeNodePath));
					$subPageNode = $pageNode->createNode($nodeName, 'TYPO3.TYPO3:Page');
					if (!$subPageNode->hasProperty('title')) {
						$subPageNode->setProperty('title', $nodeName);
					}
				}
				$pageNode = $subPageNode;
			}
			$sectionNode = $pageNode->getNode('main');
			if ($sectionNode === NULL) {
				$this->outputLine('Creating section node "%s"', array($relativeNodePath . '/main'));
				$sectionNode = $pageNode->createNode('main', 'TYPO3.TYPO3:Section');
			}
			$textNode = $sectionNode->getNode('text1');
			if ($textNode === NULL) {
				$this->outputLine('Creating text node "%s"', array($relativeNodePath . '/main/text1'));
				$textNode = $sectionNode->createNode('text1', 'TYPO3.TYPO3:Text');
			}
			$pageNode->setProperty('title', $data->title);
			$this->outputLine('Setting page title of page "%s" to "%s"', array($relativeNodePath, $data->title));
			$bodyText = $this->prepareBodyText($data->body, $relativeNodePath);
			$textNode->setProperty('text', $bodyText);
		}
		$this->siteRepository->update($contentContext->getCurrentSite());
	}

	/**
	 * @param string $bodyText
	 * @return string
	 */
	protected function prepareBodyText($bodyText, $relativeNodePath) {
		$bodyText = $this->replaceLinks($bodyText, $relativeNodePath);
		$bodyText = $this->replaceImages($bodyText);

		return $bodyText;
	}

	/**
	 * @param string $bodyText
	 * @return string
	 */
	protected function replaceLinks($bodyText, $relativeNodePath) {
		$self = $this;
		$configuration = $this->bundleConfiguration;
		$bodyText = preg_replace_callback('/(<a .*?href=")\.\.\/([^#"]*)/', function($matches) use($self, $configuration, $relativeNodePath) {
			$path = $self->normalizeNodePath($matches[2]);
				echo $configuration['importRootNodePath'] . ' || ' . $relativeNodePath . ' || ' . $path . PHP_EOL . PHP_EOL;
				//exit;
			$path = \TYPO3\FLOW3\Utility\Files::concatenatePaths(array($configuration['importRootNodePath'], $relativeNodePath , $path));
			$path = '/' . trim($path, '/') . '.html';
			$path = str_replace('/index.html', '.html', $path);
			return $matches[1] . $path;
		} , $bodyText);
		return $bodyText;
	}

	/**
	 * @param string $bodyText
	 * @return string
	 */
	protected function replaceImages($bodyText) {
		$self = $this;
		$configuration = $this->bundleConfiguration;
		$resourceManager = $this->resourceManager;
		$resourcePublisher = $this->resourcePublisher;

		$bodyText = preg_replace_callback('/(<img .*?src=")([^"]*)(".*?\/>)/', function($matches) use($self, $configuration, $resourceManager, $resourcePublisher) {
			$imageRootPath = isset($configuration['imageRootPath']) ? $configuration['imageRootPath'] : \TYPO3\FLOW3\Utility\Files::concatenatePaths(array($configuration['renderedDocumentationRootPath'], '_images'));
			$imagePathAndFilename = \TYPO3\FLOW3\Utility\Files::concatenatePaths(array($imageRootPath, basename($matches[2])));
			$imageResource = $resourceManager->importResource($imagePathAndFilename);
			$image = new \TYPO3\Media\Domain\Model\Image($imageResource);
			if ($image->getWidth() > $configuration['imageMaxWidth'] || $image->getHeight() > $configuration['imageMaxHeight']) {
				$image = $image->getThumbnail($configuration['imageMaxWidth'], $configuration['imageMaxHeight']);
			}
			$imageUri = '_Resources/' . $resourcePublisher->publishPersistentResource($image->getResource());
			if ($image->getWidth() > $configuration['thumbnailMaxWidth'] || $image->getHeight() > $configuration['thumbnailMaxHeight']) {
				$thumbnail = $image->getThumbnail(710, 800);
				$thumbnailUri = '_Resources/' . $resourcePublisher->getPersistentResourceWebUri($thumbnail->getResource());
				return sprintf('<a href="%s" class="lightbox">%s%s" style="width: %dpx" /></a>', $imageUri, $matches[1], $thumbnailUri, $thumbnail->getWidth());
			} else {
				return sprintf('%s%s" style="width: %dpx" />', $matches[1], $imageUri, $image->getWidth());
			}
		} , $bodyText);
		return $bodyText;
	}

	/**
	 * @param string $nodePath
	 * @return string
	 */
	public function normalizeNodePath($nodePath) {
		$nodePath = strtolower($nodePath);
		$nodePath = preg_replace('/(?<=^|\/)index(?=$)/', '', $nodePath);
		return $nodePath;
	}
}

?>