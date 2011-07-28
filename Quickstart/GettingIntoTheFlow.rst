Getting Into the FLOW
=====================

What Is in This Guide?
----------------------

This guided tour gets you started with FLOW3 by giving step-by-step instructions for the
development of a small sample application. It will give you a good overview of the basic
concepts and leaves the details to the full manual and more specific guides.

What Is FLOW3?
--------------

FLOW3 is a PHP-based application framework which is especially well-suited for
enterprise-grade applications. Its architecture and conventions keep your head clear and 
let you focus on the essential parts of your application. Although stability, security and
performance are all important elements of the framework's design, the fluent user
experience is the one underlying theme which rules them all.

As a matter of fact, FLOW3 is easier to learn for PHP beginners than for veterans. It
takes a while to leave behind old paradigms and open up for new approaches. That being
said, developing with FLOW3 is very intuitive and the basic principles can be learned
within a few hours. Even if you don't decide to use FLOW3 for your next project, there are
a lot of universal development techniques you can learn.

.. tip::

	This tutorial goes best with a Caffè Latte or, if it's afternoon or late night 
	already, with a few shots of Espresso ...

Downloading FLOW3
-----------------

Setting up FLOW3 is pretty straight-forward. As a minimum requirement you will need:

* A web server (we recommend Apache with the *mod_rewrite* module enabled)
* PHP 5.3.2 or later
* A PDO-compatible database such as MySQL
* Command line access

Download the `FLOW3 Base Distribution`_ and unpack it in a directory which will be
accessible by your web server. You will end up with a directory structure like this:

.. code-block:: text

	htdocs/               <-- depending on your web server
	  Quickstart/         <-- depending on which directory you chose
	    Build/
	    Configuration/
	      Settings.yaml.example
	      ...
	    Packages/
	      Framework/
	        TYPO3.FLOW3/
	        ...
	    Web/              <-- your virtual host root will later point to this
	      .htaccess
	      index.php
	      flow3
	      flow3.bat

Setting File Permissions
------------------------

You will access FLOW3 from both, the command line and the web browser. In order to provide
write access to certain directories for both, you will need to set the file permissions
accordingly. But don't worry, this is simply done by changing to the FLOW3 base directory
(``Quickstart`` in the above example) and calling the following command:

.. code-block:: bash

	./flow3 core:setfilepermissions john www-data www-data

Please replace *john* by your own username. The second argument is supposed to be the
username of your web server and the last one specifies the web server's group. For most
installations on Mac OS X this would be both *_www* instead of *www-data*.

.. note::

	Setting file permissions is not necessary and not possible on Windows machines.

Testing the Installation
------------------------

.. figure:: /Images/Welcome.png
	:align: right
	:width: 200pt
	:alt: The FLOW3 Welcome Screen
	
	The FLOW3 Welcome Screen

If your system is configured correctly you should now be able to access the Welcome
screen. Just point your browser to the ``Web`` directory of your FLOW3 installation,
for example:

.. code-block:: text

	http://localhost/Quickstart/Web/

The result should look similar to the screen you see in the screenshot. If something went
wrong, it usually can be blamed on a misconfigured web server or insufficient file
permissions.

.. tip::

	There are some friendly ghosts in our `IRC channel`_ and in the
	`users mailing list`_ –  they will gladly help you out if describe your problem as
	precisely as possible.

Kickstarting a Package
----------------------

The actual code of an application and its resources – such as images, style sheets and
templates – are bundled into *packages*. Each package is identified by a globally unique
package key, which consists of your company or domain name (the so called *vendor name*)
and further parts you choose for naming the package.

Let's create a *Demo* package for our fictive company *Acme*:

.. code-block:: bash

	$ ./flow3 kickstart:package Acme.Demo
	Created .../Acme.Demo/Classes/Controller/StandardController.php
	Created .../Acme.Demo/Resources/Private/Templates/Standard/Index.html

The Kickstarter will create a new package directory in *Packages/Application/* resulting
in the following structure:

.. code-block:: text

	Packages/
	  Application/
	    Acme.Demo/
	      Classes/
	      Configuration/
	      Documentation/
	      Meta/
	      Resources/

The :command:`kickstart:package` command also generates a sample controller which displays
some content. You should be able to access it through the following URL:

.. code-block:: text

	http://localhost/Quickstart/Web/Acme.Demo

Hello World
-----------

Let's use the *StandardController* for some more experiments. After opening the respective
class file in *Packages/Application/Acme.Demo/Classes/Controller/* you should find the
method *indexAction()* which is responsible for the output you've just seen in your web
browser::

	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('foos', array(
			'bar', 'baz'
		));
	}

Accepting some kind of user input is essential for most applications and FLOW3 does a
great deal of processing and sanitizing any incoming data. Try it out – create a new
action method like this one::

	/**
	 * Hello action
	 *
	 * @param string $name Your name
	 * @return string The hello
	 */
	public function helloAction($name) {
		return "Hello $name!";
	}

.. important::

	Always make sure to properly document all your functions and class properties. This 
	will not only help other developers to understand your code, but is also essential for
	FLOW3 to work properly: In the above example FLOW3 will, for example, determine that
	the expected type of the parameter *$name* is *string* and adjust some validation
	rules accordingly.

Now test the new action by passing it a name like in the following URL:

.. code-block:: text

	http://localhost/Quickstart/Web/Acme.Demo/Standard/hello?name=Robert

The path segments of this URL tell FLOW3 to which controller and action the web request
should be dispatched to. In our example the parts are:

* *Acme.Demo* (package key)
* *Standard* (controller name)
* *hello* (action name)

If everything went fine, you should be greeted by a friendly "`Hello John!`" – if that's
the name you passed to the action. Also try leaving out the *name* parameter in the URL –
FLOW3 will complain about a missing argument.

Storing and Retrieving Objects
------------------------------

One important design goal for FLOW3 was to let a developer focus on the business logic and
work in a truly object-oriented fashion. While you develop a FLOW3 application, you will
hardly note that content is actually stored in a database. Your code won't contain any
SQL query and you don't have to deal with setting up table structures.

But before you can store anything, you still need to set up a database and tell FLOW3 how
to access it. The credentials and driver options need to be specified in the global 
FLOW3 settings.

After you have created an empty database and set up a user with sufficient access 
rights, copy the file *Configuration/Settings.yaml.example* and save it as
*Settings.yaml*. Open and adjust the file to your needs – for a common MySQL setup, it would
look similar to this:

.. code-block:: yaml

	TYPO3:
	  FLOW3:
	    persistence:
	     backendOptions:
	      driver: 'pdo_mysql'
	      dbname: 'phoenix'    # adjust to your database name
	      user: 'root'         # adjust to your database user
	      password: 'password' # adjust to your database password
	      host: '127.0.0.1'    # adjust to your database host
	      path: '127.0.0.1'    # adjust to your database host
	      port: 3306

.. note::

	If you have never written :term:`YAML`, there are two things you should know at least:
	
	* indentation has a meaning: by different levels of indentation, a structure is
	  defined.
	* spaces, no tabs: you must indent with exactly 2 spaces per level, don't use tabs.

.. _FLOW3 Base Distribution:                       http://flow3.typo3.org/download
.. _IRC channel:                                                   http://flow3.typo3.org/get-involved/irc-channel/
.. _users mailing list:                                   http://flow3.typo3.org/get-involved/mailing-lists-newsgroups/
