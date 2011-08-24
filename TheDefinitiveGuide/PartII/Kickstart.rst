=========
Kickstart
=========

FLOW3 makes it easy to start with a new application. The ``Kickstart`` package provides
template based scaffolding for generating an initial layout of packages, controllers,
models and views.

.. note::

	At the time of this writing these functions are only available through
	FLOW3's command line interface. Please note that this might change in the
	future because the philosophy of FLOW3 is using

	- the command line for **automatization** and **system administrative tasks and**
	- a clear web interface for **modeling** and **development**


Command Line Tool
=================

The script *flow3* resides in the main directory of the FLOW3 distribution.
From a shell you should be able to run the script by entering ``./flow3``:

console::

	myhost:tutorial johndoe$ ./flow3
	FLOW3 1.0.0-beta1 (Development)
	usage: ./flow3 <command identifier>

	The following commands are currently available:

	PACKAGE "TYPO3.FLOW3":

	  ⚒ typo3.flow3:cache:flush                             Flush all caches
		typo3.flow3:cache:warmup                            Warm up caches

	  ⚒ typo3.flow3:core:setfilepermissions                 Adjust file permissions for CLI and web server access
	  ⚒ typo3.flow3:core:shell                              Run the interactive Shell

		typo3.flow3:doctrine:validate                       Validate the class/table mappings
		typo3.flow3:doctrine:create                         Create the database schema based on current mapping information
		typo3.flow3:doctrine:update                         Update the database schema, not using migrations
		typo3.flow3:doctrine:compileproxies                 Compile the Doctrine proxy classes
		typo3.flow3:doctrine:entitystatus                   Show the current status of entities and mappings
		typo3.flow3:doctrine:dql                            Run arbitrary DQL and display results
		typo3.flow3:doctrine:migrationstatus                Show the current migration status
		typo3.flow3:doctrine:migrate                        Migrate the database schema
		typo3.flow3:doctrine:migrationexecute               Execute a single migration
		typo3.flow3:doctrine:migrationversion               Mark/unmark a migration as migrated
		typo3.flow3:doctrine:migrationgenerate              Generate a new migration

		typo3.flow3:help:help                               Display help for a command

		typo3.flow3:package:create                          Create a new package
		typo3.flow3:package:delete                          Delete an existing package
		typo3.flow3:package:activate                        Activate an available package
		typo3.flow3:package:deactivate                      Deactivate a package
		typo3.flow3:package:list                            List available packages

		typo3.flow3:routing:list                            List the known routes

		typo3.flow3:security:importpublickey                Import a public key
		typo3.flow3:security:importprivatekey               Import a private key

	PACKAGE "TYPO3.KICKSTART":

		typo3.kickstart:kickstart:package                   Kickstart a new package
		typo3.kickstart:kickstart:actioncontroller          Kickstart a new action controller
		typo3.kickstart:kickstart:commandcontroller         Kickstart a new command controller
		typo3.kickstart:kickstart:model                     Kickstart a new domain model
		typo3.kickstart:kickstart:repository                Kickstart a new domain repository

	⚒ compile time command

Depending on your FLOW3 version you'll see more or less the above available
commands listed.


Kickstart the package
=====================

Let's create a new package **Blog** inside the Vendor namespace **TYPO3**:

console::

	myhost:tutorial johndoe$ ./flow3 typo3.kickstart:kickstart:package TYPO3.Blog

The kickstarter will create two files

console::

	+ .../Packages/Application/TYPO3.Blog/Classes/Controller/StandardController.php
	+ ...tandard/Index.html

and the directory *Packages/Application/TYPO3.Blog/* should now contain the
skeleton of the future ``Blog`` package:

console::

	myhost:tutorial johndoe$ ``cd Packages/Application/``
	myhost:Application johndoe$ ``find TYPO3.Blog``
	TYPO3.Blog
	TYPO3.Blog/Configuration
	TYPO3.Blog/Tests
	TYPO3.Blog/Tests/Unit
	TYPO3.Blog/Tests/Functional
	TYPO3.Blog/Documentation
	TYPO3.Blog/Classes
	TYPO3.Blog/Classes/Package.php
	TYPO3.Blog/Classes/Controller
	TYPO3.Blog/Classes/Controller/StandardController.php
	TYPO3.Blog/Resources
	TYPO3.Blog/Resources/Private
	TYPO3.Blog/Resources/Private/Templates
	TYPO3.Blog/Resources/Private/Templates/Standard
	TYPO3.Blog/Resources/Private/Templates/Standard/Index.html
	TYPO3.Blog/Meta
	TYPO3.Blog/Meta/Package.xml


Switch to your web browser and check if the generated controller produces some output:

.. image: /Images/GettingStarted/FreshBlogPackage.png

.. tip::
	If you get an error at this point, like a "404 Not Found" this could be
	caused by outdated cache entries. Because FLOW3 should be running in
	``Development`` context at this point, it is supposed to detect changes to
	code and resource files, but this seems to sometimes fail... Before you go
	crazy looking for an error on your side, **try clearing the cache manually**
	by removing the contents of *Data/Temporary/*.

Kickstart Controllers
=====================

If you look at the drawing of our overall model you'll notice that you need
controllers for the most important domain models, being ``Post`` and ``Comment``.
We also need a ``SetupController`` which initially sets up the blog. Create them
with the kickstarter as well:

console::

	myhost:tutorial johndoe$ ./flow3 typo3.kickstart:kickstart;controller TYPO3.Blog --controllerName Setup,Post,Comment

resulting in:

console::

	+ .../Packages/Application/Blog/Classes/Controller/SetupController.php
	+ ...etup/Index.html
	+ .../Packages/Application/Blog/Classes/Controller/PostController.php
	+ ...ost/Index.html
	+ .../Packages/Application/Blog/Classes/Controller/CommentController.php
	+ ...omment/Index.html

These new controllers can now be accessed via

	- http://dev.tutorial.local/typo3.blog/setup,
	- http://dev.tutorial.local/typo3.blog/post and
	- http://dev.tutorial.local/typo3.blog/comment

respectively.

Please delete the file *StandardController.php* and its corresponding template
directory as you won't need them for our sample application.

Kickstart Models and Repositories
=================================

The kickstarter can also generate models and repositories::
		``./flow3 typo3.kickstart:kickstart:model PackageKey ModelName propertyName:type propertyName:type``

However, at this point you will stop using the kickstarter because writing models and
repositories by hand is really easy.
