=============
Configuration
=============

.. ============================================
.. Meta-Information for this chapter
.. ---------------------------------
.. Author: Robert Lemke
.. Converted to ReST by: Rens Admiraal
.. Updated for 1.0 beta1: NO
.. TODOs: none
.. ============================================

Configuration Framework
=======================

Configuration is an important aspect of versatile applications. FLOW3 provides you with
configuration mechanisms which have a small footprint and are convenient to use and
powerful at the same time. Hub for all configuration is the configuration manager which
handles alls configuration tasks like reading configuration, configuration cascading, and
(later) also writing configuration.

Configuration Files
-------------------

FLOW3 distinguishes between different types of configuration. The most important type of
configuration are the settings, however other configuration types exist for special
purposes.

The preferred configuration format is YAML and the configuration options of each type are
defined in their own dedicated file:

* **Settings.yaml**
	Contains user-level settings, i.e. configuration options the users or administrators
	are meant to change. Settings are the highest level of system configuration.
* **Routes.yaml**
	Contains routes configuration. This routing information is parsed and used by the MVC
	Web Routing mechanism. Refer to the MVC section for more information.
* **Objects.yaml**
	Contains object configuration, i.e. options which configure objects and the
	combination of those on a lower level. See the Object Manager section for more
	information.
* **SignalsSlots.yaml**
	Contains mapping information between signals and slots. More about this mechanism can
	be found in the Signal Slots section.
* **Security.yaml**
	(not yet implemented)
* **Package.yaml**
	Contains package configuration, i.e. options which define certain specialties of the
	package such as custom autoloaders or special resources.
* **PackageStates.yaml**
	Contains a list of packages and their current state, for  example if they are active
	or not. Don't edit this file directly, rather use the *flow3* command line tool do
	activate and deactivate packages.
* **Caches.yaml**
	Contains a list of caches which are registered automatically. Caches defined in this
	configuration file are registered in an early stage of the boot process and profit
	from mechanisms such as automatic flushing by the File Monitor.

File Locations
~~~~~~~~~~~~~~

There are several locations where configuration files may be placed. All of them are
scanned by the configuration manager during initialization and cascaded into a single
configuration tree. The following locations exist (listed in the order they are loaded):

* **/Packages/<replaceable>PackageName</replaceable>/Configuration/**
	The *Configuration* directory of each package is scanned first. Only at this stage new
	configuration options can be introduced (by just defining a default value). After all
	configuration files form these directories have been parsed, the resulting
	configuration containers are protected against further introduction of new options.
* **/Configuration/**
	Configuration in the global *Configuration* directory override the default settings
	which were defined in the package's configuration directories. To safe users from
	typos, options which are introduced on this level will result in an error message.
* **/Configuration/<ApplicationContext>/**
	There may exist a subdirectory for each application context (see FLOW3 Bootstrap
	section). This configuration is only loaded if FLOW3 runs in the respective
	application context. Like in the global *Configuration* directory, no new
	configuration options can be introduced at this point - only their values can be
	changed.

Defining Configuration
----------------------

Configuration Format
~~~~~~~~~~~~~~~~~~~~

The format of FLOW3's configuration files is YAML. YAML is a well-readable format which is
especially well-suited for defining configuration. The full specification among with many
examples can be found on the `YAML website`_. All important parts of the YAML
specification are supported by the parser used by FLOW3, it might happen though that some
exotic features won't have the desired effect. At best you look at the configuration files
which come with the FLOW3 distribution for getting more examples.

*Example: a package-level Settings.yaml*

.. code-block:: yaml

	#                                                                        #
	# Settings Configuration for the TYPO3CR Package                         #
	#                                                                        #

	# $Id: Settings.yaml 1234 2009-01-01 12:00:00Z foobar $

	TYPO3CR:

	  # The storage backend configuration
	  storage:
	    backend: 'TYPO3\TYPO3CR\Storage\Backend\Pdo'
	    backendOptions:
	      dataSourceName: 'sqlite:%FLOW3_PATH_DATA%Persistent/TYPO3CR.db'
	      username: 
	      password: 

	  # The indexing/search backend configuration
	  search:
	    backend: 'TYPO3\TYPO3CR\Storage\Search\Lucene'
	    backendOptions:
	      indexLocation: '%FLOW3_PATH_DATA%Persistent/Index/'

Constants
~~~~~~~~~

Sometimes it is necessary to use values in your configuration files which are defined as
PHP constants.These values can be included by special markers which are replaced by the
actual value during parse time. The format is ``%<CONSTANT_NAME>%`` where
``<CONSTANT_NAME>`` is the name of a PHP constant. Note that the constant name must be all
uppercase.

Some examples:

* *%FLOW3_PATH_WEB%*
	Will be replaced by the path to the public web directory.
* *%PHP_VERSION%*
	Will be replaced by the current PHP version.

Accessing Configuration
-----------------------

There are certain situations in which FLOW3 will automatically provide you with the right
configuration - the MVC's Action Controller is such a case. However, in most other cases
you will have to retrieve the configuration yourself. The Configuration Manager comes up
with a very simple API providing you access to the already parsed and cascaded
configuration.

Working with Settings
~~~~~~~~~~~~~~~~~~~~~

What you usually want to work with are settings. The following example demonstrates how to
let FLOW3 inject the settings of a classes' package and output some option value:

*Example: Settings Injection* ::

	namespace TYPO3\Demo;

	class SomeClass {

		/**
		 * @var array
		 */
		protected $settings;

		/**
		 * Inject the settings
		 *
		 * @param array $settings
		 * @return void
		 */
		public function injectSettings(array $settings) {
			$this->settings = $settings;
		}

		/**
		 * Outputs some settings of the "Demo" package.
		 *
		 * @return void
		 */
		public function theMethod() {
			echo ($this->settings['administrator']['name']);
			echo ($this->settings['administrator']['email']);
		}
	}

Manually Retrieving Settings
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There might be situations in which you don't want to get the settings injected. The
Configuration Manager provides an API for these cases as you can see in the next example.

*Example: Retrieving settings* ::

	namespace TYPO3\Demo;

	class SomeClass {

		/**
		 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 	 */
		protected $configurationManager;

		/**
		 * Inject the Configuration Manager
		 *
		 * @param \TYPO3\FLOW3\Configuration\ConfigurationManager $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager( ⏎
		\TYPO3\FLOW3\Configuration\ConfigurationManager ⏎
	    $configurationManager) {
			$this->configurationManager = $configurationManager;
		}

		/**
		 * Output some settings of the Demo package
		 *
		 * @return void
		 */
		public function theMethod() {
			$mySettings = $this->configurationManager->getConfiguration( ⏎
			\TYPO3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, ⏎
			'Demo');
			echo ($mySettings->administrator->name);
			echo ($mySettings->administrator->email);
		}
	}

Working with other configuration
--------------------------------

Although infrequently necessary, it is also possible to retrieve options of the more
special configuration types. The configuration manager provides a method called
``getConfiguration()`` for this purpose. The result this method returns depends on the
actual configuration type you are requesting.

Bottom line is that you should be highly aware of what you're doing when working with
these special options and that they might change in a later version of FLOW3. Usually
there are much better ways to get the desired information (e.g. ask the Object Manager for
object configuration).

Configuration Cache
-------------------

Parsing the YAML configuration files takes a bit of time which remarkably slows down the
initialization of FLOW3. That's why all configuration is cached by default when FLOW3 is
running in Production context. Because this cache cannot be cleared automatically it is
important to know that changes to any configuration file won't have any effect until you
manually flush the respective caches.

This feature can be configure through a switch in the *Settings.yaml* file:

.. code-block:: yaml

	TYPO3:
	  FLOW3:
	    configuration:
	      compileConfigurationFiles: y

When enabled, the configuration manager will compile all loaded configuration into a PHP
file which will be loaded in subsequent calls instead of parsing the YAML files again.

.. important::

	Once the configuration is cached changes to the YAML files don't have any effect.
	Therefore in order to switch off the configuration cache again you need to disable the
	feature in the YAML file *and* flush all caches afterwards manually.

.. _YAML website:        ???