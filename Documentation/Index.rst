TYPO3.DocTools Documentation
============================

INSTALLATION
------------

We need *Sphinx* (http://sphinx.pocoo.org/) to render documentation.
If you have python easy_install available, use the following command: ::

	easy_install -U Sphinx

Bundles
-------

The DocTools package renders based on bundle configurations like ::

	TYPO3:
	  DocTools:
	    bundles:
	      TYPO3DocToolsHtml:
	        documentationRootPath: %FLOW3_PATH_PACKAGES%Documentation/TYPO3.DocTools/Documentation/
	        configurationRootPath: %FLOW3_PATH_PACKAGES%Documentation/TYPO3.DocTools/Resources/Private/Themes/TYPO3/
	        renderedDocumentationRootPath: %FLOW3_PATH_DATA%Temporary/Documentation/TYPO3.DocTools/
	        renderingOutputFormat: 'html'
	      ImportToAPhoenixSite:
	        importRootNodePath: 'documentation/quickstart'
	        documentationRootPath: %FLOW3_PATH_PACKAGES%Documentation/TYPO3.DocTools/Documentation/
	        configurationRootPath: %FLOW3_PATH_PACKAGES%Documentation/TYPO3.DocTools/Resources/Private/Themes/TYPO3/
	        renderedDocumentationRootPath: %FLOW3_PATH_DATA%Temporary/Documentation/TYPO3.DocTools/

Those bundles can be rendered by the following command ::

	./flow3 documentation:render [--bundle <bundle>]

An import to a Phoenix website can be executed using ::

	./flow3 documentation:import [--bundle <bundle>]

TYPO3 Publication Style Guide
-----------------------------

A style guide giving advice on how to write for the TYPO3 project.

.. toctree::
	:maxdepth: 2

	StyleGuide/Index

Contributing to the Documentation
---------------------------------

.. toctree::
	:maxdepth: 2

	Contributing
