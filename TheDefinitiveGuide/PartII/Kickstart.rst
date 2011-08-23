=========
Kickstart
=========

FLOW3 makes it easy to start with a new application. The
  ``Kickstart`` package provides template based scaffolding for
  generating an initial layout of packages, controllers, models and
  views.

.. note::

	At the time of this writing these functions are only available through
	FLOW3's command line interface. Please note that this might change in the
	future because the philosophy of FLOW3 is using

	- the command line for **automatization** and **system administrative tasks and**
	- a clear web interface for **modeling** and **development**


Command Line Tool
=================

.. warning:: |documentationNotReady|

The script *flow3* resides in the main directory of the FLOW3 distribution.
From a Unix shell you should be able to run the script by entering ``./flow3``:

console::

	myhost:tutorial johndoe$ ./flow3
	FLOW3 1.0.0-beta1 (Development)
	usage: ./flow3 &lt;command identifier>

	The following commands are currently available:

	PACKAGE "TYPO3.FLOW3":

		typo3.flow3:cache:flush                             Flush all caches

		typo3.flow3:core:compile                            Explicitly compile proxy classes
		typo3.flow3:core:shell                              Run the interactive Shell

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
		typo3.flow3:package:listavailable                   List available (active and inactive) packages
		typo3.flow3:package:listactive                      List active packages

		typo3.flow3:routing:list                            List the known routes

		typo3.flow3:security:importpublickey                Import a public key
		typo3.flow3:security:importprivatekey               Import a private key

	PACKAGE "TYPO3.KICKSTART":

		typo3.kickstart:kickstart:package                   Kickstart a package
		typo3.kickstart:kickstart:controller                Kickstart a controller class
		typo3.kickstart:kickstart:model                     Kickstart a domain model
		typo3.kickstart:kickstart:repository                Kickstart a domain repository



Depending on your FLOW3 version you'll see more or less the above available
commands listed.

.. note::
	We haven't developed a Windows batch script yet so for the time being
	you'll have to call FLOW3 manually. Before you can run the FLOW3 command
	line script you need to set some environment variables:

console::

	c:\> set FLOW3_CONTEXT=Development
	c:\> set FLOW3_ROOTPATH=C:\xampp\htdocs\tutorial
	c:\> set FLOW3_WEBPATH=C:\xampp\htdocs\tutorial\Web

If you like to make those variable settings permanent, so they are valid for
more than just the current shell session, you can use the ``setx`` command:

console::

	c:\> setx FLOW3_CONTEXT Development
	c:\> setx FLOW3_ROOTPATH C:\xampp\htdocs\tutorial
	c:\> setX FLOW3_WEBPATH C:\xampp\htdocs\tutorial\Web

Listing the available packages is then as easy as typing:

console::

	c:\> (php Packages\Framework\FLOW3\Scripts\FLOW3.php FLOW3 Package PackageManager listavailable)


Kickstart the package
=====================

.. warning:: |documentationNotReady|

Let's create a new package **Blog** inside the Vendor namespace **TYPO3**:

console::

	myhost:tutorial johndoe$ ./flow3 typo3.kickstart:kickstart:package TYPO3.Blog

or on Windows:

console::

	c:\xampp\htdocs\tutorial> (php Packages\Framework\FLOW3\Scripts\FLOW3.php Kickstart Kickstart generatePackage --packageKey TYPO3.Blog)

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

or on Windows:

console::

	c:\xampp\htdocs\tutorial> (php Packages\Framework\FLOW3\Scripts\FLOW3.php Kickstart Kickstart generateController --packageKey Blog --controllerName "Setup,Post,Comment")

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

.. tip::
	If you can't access the newly created controllers one reason might be that
	you did not run FLOW3 in the development context (did you set the
	``FLOW3_CONTEXT`` environment variable as explained earlier?). As already
	mentioned, FLOW3 does not clear caches automatically in a production
	context so you better work in development mode while you're developing.

Please delete the file *StandardController.php* and its corresponding template
directory as you won't need them for our sample application.

Kickstart Models and Repositories
=================================

The kickstarter can also generate models and repositories [#]_\ . However, at
this point you will stop using the kickstarter because

a) writing models and repositories by hand is really easy and
b) as mentioned before, the command line won't be the preferred way of
   generating scaffolds in the future. We are not completely happy with the
   parameter syntax yet and therefore it is better not to teach it to you.


-----

.. [#]	Want to try it out? The syntax is
		``./flow3 typo3.kickstart:kickstart:model PackageKey ModelName
		propertyName:type propertyName:type``
		... or on Windows
		``php Packages\Framework\FLOW3\Scripts\FLOW3.php Kickstart Kickstart
		generateModel --packageKey Blog --modelName ModelName foo:string
		bar:integer``