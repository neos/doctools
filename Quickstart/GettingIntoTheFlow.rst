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
username of your webserver and the last one specifies the web server's group. For most
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

Let's use the *StandardController* for some more experiments. After opening the respective class file in *Packages/Application/Acme.Demo/Classes/Controller/* you should find the method *indexAction()* which is responsible for the output you've just seen in your web browser::

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

Accepting some kind of user input is essential for most applications. FLOW3 does a great
deal of processing and sanitizing any incoming data. In the *StandardController* just create a new action method like this one::

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
	

Storing and Retrieving Objects
------------------------------




.. _FLOW3 Base Distribution:                       http://flow3.typo3.org/download
.. _IRC channel:                                                   http://flow3.typo3.org/get-involved/irc-channel/
.. _users mailing list:                                   http://flow3.typo3.org/get-involved/mailing-lists-newsgroups/
