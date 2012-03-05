<?php
namespace Documentation\Command;

/*                                                                        *
 * This script belongs to the FLOW3 package "Documentation".              *
 *                                                                        *
 *                                                                        *
 */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Documentation command controller for the Documentation package
 *
 * @FLOW3\Scope("singleton")
 */
class DocumentationCommandController extends \TYPO3\FLOW3\MVC\Controller\CommandController {

	/**
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
	 * Current bundle configuration
	 *
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
	 * @param \TYPO3\TYPO3\Domain\Repository\SiteRepository $siteRepository
	 * @return void
	 */
	public function injectSiteRepository(\TYPO3\TYPO3\Domain\Repository\SiteRepository $siteRepository) {
		$this->siteRepository = $siteRepository;
	}

	/**
	 * Renders reST files to fjson files which can be processed by the import command.
	 *
	 * @param string $bundle Bundle to render. If not specified all configured bundles will be rendered
	 * @return void
	 */
	public function renderCommand($bundle = NULL) {
		$bundles = $bundle !== NULL ? array($bundle) : array_keys($this->settings['bundles']);
		$defaultConfiguration = isset($this->settings['defaultConfiguration']) ? $this->settings['defaultConfiguration'] : array();
		if ($bundles === array()) {
			$this->outputLine('No bundles configured.');
			$this->quit(1);
		}
		foreach ($bundles as $bundle) {
			if (!isset($this->settings['bundles'][$bundle])) {
				$this->outputLine('Bundle "%s" is not configured.', array($bundle));
				$this->quit(1);
			}
			$configuration = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($defaultConfiguration, $this->settings['bundles'][$bundle]);
			$this->outputLine('Rendering bundle "%s"', array($bundle));
			if (is_dir($configuration['renderedDocumentationRootPath'])) {
				\TYPO3\FLOW3\Utility\Files::removeDirectoryRecursively($configuration['renderedDocumentationRootPath']);
			}
			$renderCommand = sprintf('sphinx-build -c %s -b json %s %s', escapeshellarg($configuration['configurationRootPath']), escapeshellarg($configuration['documentationRootPath']), escapeshellarg($configuration['renderedDocumentationRootPath']));
			exec($renderCommand, $output, $result);
			if ($result !== 0) {
				$this->output('Could not execute sphinx-build command for Bundle %s', array($bundle));
				$this->quit(1);
			}
		}
	}

	/**
	 * Imports fjson files into TYPO3CR nodes.
	 * See Settings.yaml for an exemplary configuration for the documentation bundles
	 *
	 * @param string $bundle bundle to import. If not specified all configured bundles will be imported
	 * @return void
	 */
	public function importCommand($bundle = NULL) {
		$bundles = $bundle !== NULL ? array($bundle) : array_keys($this->settings['bundles']);
		$defaultConfiguration = isset($this->settings['defaultConfiguration']) ? $this->settings['defaultConfiguration'] : array();

		foreach ($bundles as $bundle) {
			if (!isset($this->settings['bundles'][$bundle])) {
				$this->outputLine('Bundle "%s" is not configured', array($bundle));
				$this->quit(1);
			}
			$this->bundleConfiguration = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($defaultConfiguration, $this->settings['bundles'][$bundle]);
			$this->importBundle($bundle);
			$this->outputLine('---');
		}
		$this->outputLine('Done');
	}

	/**
	 * Imports the specified bundle into the configured "importRootNodePath".
	 *
	 * @param string $bundle
	 * @return void
	 */
	protected function importBundle($bundle) {
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

		if (!is_dir($renderedDocumentationRootPath)) {
			$this->outputLine('The folder "%s" does not exist. Did you render the documentation?', array($renderedDocumentationRootPath));
			$this->quit(1);
		}

		$unorderedJsonFileNames = \TYPO3\FLOW3\Utility\Files::readDirectoryRecursively($renderedDocumentationRootPath, '.fjson');
		if ($unorderedJsonFileNames === array()) {
			$this->outputLine('The folder "%s" contains no fjson files. Did you render the documentation?', array($renderedDocumentationRootPath));
			$this->quit(1);
		}

		$orderedNodePaths = array();
		foreach ($unorderedJsonFileNames as $jsonPathAndFileName) {
			if(basename($jsonPathAndFileName) === 'Index.fjson') {
				$chapterRelativeNodePath = substr($jsonPathAndFileName, strlen($renderedDocumentationRootPath), -12) . '/';
#				$orderedNodePaths[] = $this->normalizeNodePath(substr($chapterRelativeNodePath, 0, -1));

				$indexArray = json_decode(file_get_contents($jsonPathAndFileName), TRUE);
				foreach (explode(chr(10), $indexArray['body']) as $tocHtmlLine) {
					preg_match('!^\<li class="toctree-l1"\>\<a class="reference internal" href="\.\./([a-zA-Z0-9-]+)/.*$!', $tocHtmlLine, $matches);
					if ($matches !== array()) {
						$orderedNodePaths[] = $this->normalizeNodePath($chapterRelativeNodePath . $matches[1]);
					}
				}
			}
		}

		foreach ($unorderedJsonFileNames as $jsonPathAndFileName) {
			$data = json_decode(file_get_contents($jsonPathAndFileName));
			if (!isset($data->body)) {
				continue;
			}
			$relativeNodePath = substr($jsonPathAndFileName, strlen($renderedDocumentationRootPath) + 1, -6);
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

		$importRootNodePath = $importRootNode->getPath();
		$currentParentNodePath = '';

		foreach ($orderedNodePaths as $nodePath) {
			$node = $importRootNode->getNode($importRootNodePath . $nodePath);
			if ($node !== NULL) {
				if ($node->getParent()->getPath() !== $currentParentNodePath) {
					$currentParentNodePath = $node->getParent()->getPath();
					$previousNode = NULL;
				}
				if ($previousNode !== NULL) {
					$this->outputLine('Moved node %s', array($node->getPath()));
					$this->outputLine('after node %s', array($previousNode->getPath()));
					$node->moveAfter($previousNode);
				} else {
					// FIXME: Node->isFirst() or Node->moveFirst() would be needed here
				}
				$previousNode = $node;
			} else {
				$this->outputLine('Node %s does not exist.' , array($importRootNodePath . $nodePath));
			}
		}

		$this->siteRepository->update($contentContext->getCurrentSite());
	}

	/**
	 * Prepares the body text before importing it into a TYPO3CR node (fixing links, images, ...)
	 *
	 * @param string $bodyText
	 * @param string $relativeNodePath
	 * @return string the modified body text
	 */
	protected function prepareBodyText($bodyText, $relativeNodePath) {
		$bodyText = $this->replaceRelativeLinks($bodyText, $relativeNodePath);
		$bodyText = $this->replaceAnchorLinks($bodyText, $relativeNodePath);
		$bodyText = $this->replaceImages($bodyText);

		return $bodyText;
	}

	/**
	 * Replaces relative links (<a href="../Xyz">) by proper page links (<a href="documentation/bundle/xyz.html">)
	 *
	 * @param string $bodyText
	 * @param string $relativeNodePath
	 * @return string the text with replaced relative links
	 */
	protected function replaceRelativeLinks($bodyText, $relativeNodePath) {
		$self = $this;
		$configuration = $this->bundleConfiguration;
		$bodyText = preg_replace_callback('/(<a .*?href=")\.\.\/([^#"]*)/', function($matches) use($self, $configuration, $relativeNodePath) {
			$path = $self->normalizeNodePath($matches[2]);
			$path = \TYPO3\FLOW3\Utility\Files::concatenatePaths(array($configuration['importRootNodePath'], $relativeNodePath , $path));
			$path = '/' . trim($path, '/') . '.html';
			$path = str_replace('/index.html', '.html', $path);
			return $matches[1] . $path;
		} , $bodyText);
		return $bodyText;
	}

	/**
	 * Replaces anchor links (<a href="#anchor">) by proper prepending the relative node path (<a href="documentation/bundle/xyz.html#anchor">)
	 *
	 * @param string $bodyText
	 * @param string $relativeNodePath
	 * @return string the text with replaced relative links
	 */
	protected function replaceAnchorLinks($bodyText, $relativeNodePath) {
		$configuration = $this->bundleConfiguration;
		$bodyText = preg_replace_callback('/(<a .*?href=")(#[^"]*)/', function($matches) use($configuration, $relativeNodePath) {
			$path = \TYPO3\FLOW3\Utility\Files::concatenatePaths(array($configuration['importRootNodePath'], $relativeNodePath));
			$path = '/' . trim($path, '/') . '.html';
			$path = str_replace('/index.html', '.html', $path);
			return $matches[1] . $path . $matches[2];
		} , $bodyText);
		return $bodyText;
	}

	/**
	 * Replaces images (<img src="Foo.png">) by persistent resources (<img src="_Resources/....">)
	 *
	 * @param string $bodyText
	 * @return string the text with replaced image tags
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
			$imageUri = $resourcePublisher->publishPersistentResource($image->getResource());
			if ($image->getWidth() > $configuration['thumbnailMaxWidth'] || $image->getHeight() > $configuration['thumbnailMaxHeight']) {
				$thumbnail = $image->getThumbnail(710, 800);
				$thumbnailUri = $resourcePublisher->getPersistentResourceWebUri($thumbnail->getResource());
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