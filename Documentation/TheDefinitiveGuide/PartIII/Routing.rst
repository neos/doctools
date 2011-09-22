.. _ch-routing:

=======
Routing
=======

.. ============================================
.. Meta-Information for this chapter
.. ---------------------------------
.. Author: Bastian Waidelich ?
.. Converted to ReST by: Rens Admiraal
.. Updated for 1.0 beta1: YES, by Sebastian Kurfürst
.. TODOs: none
.. ============================================

As explained in the Model View Controller chapter, in FLOW3 the dispatcher passes the
request to a controller which then calls the respective action. But how to tell, what
controller of what package is the right one for the current request? This is were the
routing framework comes into play.

The Router
==========

The request builder asks the router for the correct package, controller and action. For
this it passes the current request path to the routers ``match()`` method. The router then
iterates through all configured routes and invokes their ``matches()`` method. The first
route that matches, determines which action will be called with what parameters.

The same works for the opposite direction: If a link is generated the router calls the
``resolve()`` method of all routes until one route can return the correct URI for the
specified arguments.

.. note::

	If no matching route can be found, the ``indexAction()`` of the ``StandardController``
	of the *FLOW3* package is called.

Routes
======

A route describes the way from your browser to the controller - and back.

With the ``uriPattern`` you can define how a route is represented in the browser's address
bar. By setting ``defaults`` you can specify package, controller and action that should
apply when a request matches the route. Besides you can set arbitrary default values that
will be available in your controller. They are called ``defaults`` because you can overwrite
them by so called *dynamic route parts*.

But let's start with an easy example:

*Example: Simple route - Routes.yaml*

.. code-block:: yaml

	--
	  name: 'Homepage'
	  uriPattern: ''
	  defaults:
	    '@package': Demo

.. note::

	``name`` is optional, but it's recommended to set a name for all routes to make debugging
	easier.

If you insert these lines at the beginning of the file ``Configurations/Routes.yaml``,
the ``indexAction`` of the ``StandardController`` in your *Demo* package will be called
when you open up the homepage of your FLOW3 installation (``http://localhost/``).

.. note::

	You don't have to specify action and controller in this example as the ``indexAction``
	of the ``StandardController`` is always called by default.

URI patterns
============

The URI pattern defines the appearance of the URI. In a simple setup the pattern only
consists of *static route parts* and is equal to the actual URI (without protocol and
host).

In order to reduce the amount of routes that have to be created, you are allowed to insert
markers, so called *dynamic route parts*, that will be repaced by the routing framework.
You can even mark route parts *optional*.

But first things first.

Static route parts
------------------

A static route part is really simple - it will be mapped one-to-one to the resulting URI
without transformation.

Let's create a route that calls the ``listAction`` of the ``CustomerController`` when browsing to
``http://localhost/my/demo``:

* Example: Simple route with static route parts Configuration/Routes.yaml*

.. code-block:: yaml

	--
	  name: 'Static demo route'
	  uriPattern: 'my/demo'
	  defaults:
	    '@package':    Demo
	    '@controller': Customer
	    '@action':     list

Dynamic route parts
-------------------

Dynamic route parts are enclosed in curly brackets and define parts of the URI that are
not fixed.

Let's add some dynamics to the previous example:

*Example: Simple route with static and dynamic route parts - Configuration/Routes.yaml*

.. code-block:: yaml

	--
	  name: 'Dynamic demo route'
	  uriPattern: 'my/demo/{@action}'
	  defaults:
	    '@package':    Demo
	    '@controller': Customer

Now ``http://localhost/my/demo/list`` calls the ``listAction`` just like in the previous
example.

With ``http://localhost/my/demo/index`` you'd invoke the ``indexAction`` and so on.

.. note::

	It's not allowed to have successive dynamic route parts in the URI pattern because it
	wouldn't be possible to determine the end of the first dynamic route part then.

The ``@``-prefix should reveal that *action* has a special meaning here. Other predefined keys
are ``@package``, ``@subpackage``, ``@controller`` and ``@format``. But you can use dynamic route parts to
set any kind of arguments:

*Example: dynamic parameters - Configuration/Routes.yaml*

.. code-block:: yaml

	--
	  name: 'Dynamic demo route'
	  uriPattern: 'clients/{sortOrder}.{@format}'
	  defaults:
	    '@package':    Demo
	    '@controller': Customer
	    '@action':     list

Browsing to ``http://localhost/clients/descending.xml`` will then call the ``listAction`` in
your ``Customer`` controller and the request argument ``sortOrder`` has the value of
``descending``.

By default, dynamic route parts match anything apart from empty strings. If you have more
specialized requirements you can create your custom *route part handlers*, as described
in the following section.

Route Part Handlers
===================

Route part handlers are classes that implement
``TYPO3\FLOW3\MVC\Web\Routing\DynamicRoutePartInterface``. But for most cases it will be
sufficient to extend ``TYPO3\FLOW3\MVC\Web\Routing\DynamicRoutePart`` and overwrite the
methods ``matchValue`` and ``resolveValue``.

Let's have a look at the (very simple) route part handler of the blog example:

*Example: BlogRoutePartHandler.php* ::

	class BlogRoutePartHandler extends \TYPO3\FLOW3\MVC\Web\Routing\DynamicRoutePart {

		/**
		 * While matching, converts the blog title into an identifer array
		 *
		 * @param string $value value to match, the blog title
		 * @return boolean TRUE if value could be matched successfully, otherwise FALSE.
		 */
		protected function matchValue($value) {
			if ($value === NULL || $value === '') return FALSE;
			$this->value = array('__identity' => array('name' => $value));
			return TRUE;
		}

		/**
		 * Resolves the name of the blog
		 *
		 * @param \TYPO3\Blog\Domain\Model\Blog $value The Blog object
		 * @return boolean TRUE if the name of the blog could be resolved and stored in
		 $this->value, otherwise FALSE.
		 */
		protected function resolveValue($value) {
			if (!$value instanceof \TYPO3\Blog\Domain\Model\Blog) return FALSE;
			$this->value = $value->getName();
			return TRUE;
		}
	}

The corresponding route might look like this:

*Example: Route with route part handlers Configuration/Routes.yaml*

.. code-block:: yaml

	--
	  name: 'Blog route'
	  uriPattern: 'blogs/{blog}/{@action}'
	  defaults:
	    '@package':    Blog
	    '@controller': Blog
	  routeParts:
	    blog:
	      handler: TYPO3\Blog\RoutePartHandlers\BlogRoutePartHandler

The method ``matchValue()`` is called when translating from an URL to a request argument,
and the method ``resolveValue()`` needs to return an URL segment when being passed an object.

.. warning:: Some examples are missing here, which should explain the API better.

.. TODO: fix above warning and then remove it.

Have a look at the blog example for a working setup.

Optional route parts
====================

By putting one or more route parts in round brackets you mark them optional. The following
route matches ``http://localhost/my/demo`` and ``http://localhost/my/demo/list.html``.

*Example: Route with optional route parts - Configuration/Routes.yaml*

.. code-block:: yaml

	--
	  name: 'Dynamic demo route'
	  uriPattern: 'my/demo(/{@action}.html)'
	  defaults:
	    '@package':    'Demo'
	    '@controller': 'Customer'
	    '@action':     'list'

.. note::

	``http://localhost/my/demo/list`` won't match here, because either all optional parts
	have to match - or none.

.. note::

	You have to define default values for all optional dynamic route parts.

Case Sensitivity
================

By Default the case is not changed when creating URIs. The following example with a
username of "Kasper" will result in ``http://localhost/Users/Kasper``

*Example: Route with default case handling*

.. code-block:: yaml

	--
	  uriPattern: 'Users/{username}'
	  defaults:
	    @package:    'Demo'
	    @controller: 'Customer'
	    @action:     'show'

You can change this behavior for routes and/or dynamic route parts:

*Example: Route with customised case handling*

.. code-block:: yaml

	--
	  uriPattern: 'Users/{username}'
	  defaults:
	    @package:    'Demo'
	    @controller: 'Customer'
	    @action:     'show'
	  toLowerCase: true
	  routeParts:
	    username:
	      toLowerCase: false

The option ``toLowerCase`` will change the default behavior for this route
and reset it for the username route
part. Given the same username of "Kasper" the resulting URI will now be
``http://localhost/users/Kasper`` (note the lower case "u" in "users").

.. note::

	The predefined route parts ``@package``, ``@subpackage``, ``@controller``, ``@action`` and
	``@format`` are an exception, they're always lower cased!

Matching of incoming URIs is always done case insensitive. So both "Users/Kasper" and
"users/Kasper" will match, and the value of the dynamic part will never be changed. If you
want to handle data coming in through dynamic route parts case-insensitive, you need to
handle that in your own code.

Subroutes
=========

For security reasons and to avoid confusion, only routes configured in your global
configuration folder are active. But FLOW3 supports what we call *subroutes* enabling you to
provide custom routes with your package and reference them in the global routing setup.

Imagine following routes in the ``Routes.yaml`` file inside your demo package:

*Example: Demo Subroutes - Demo/Configuration/Routes.yaml*

.. code-block:: yaml

	--
	  name: 'Customer routes'
	  uriPattern: '/clients/{@action}'
	  defaults:
	    '@controller': Customer

	--
	  name: 'Standard routes'
	  uriPattern: '/{@action}'
	  defaults:
	    '@controller': Standard

	--
	  name: 'Fallback'
	  uriPattern: ''
	  defaults:
	    '@controller': Standard
	    '@action':     index

And in your global ``Routes.yaml``:

*Example: Referencing subroutes - Configuration/Routes.yaml*

.. code-block:: yaml

	--
	  name: 'Demo subroutes'
	  uriPattern: 'demo<DemoSubroutes>(.{@format})'
	  defaults:
	    '@package': Demo
	    '@format':  html
	  subRoutes:
	    DemoSubroutes:
	      package: Demo

As you can see, you can reference subroutes by putting parts of the URI pattern in angle
brackets (like ``<subRoutes>``). With the subRoutes setting you specify where to load the
subroutes from.

Internally the ConfigurationManager merges toghether the main route with its subroutes, resulting
in the following routing configuration:

*Example: Merged routing configuration*

.. code-block:: yaml

	--
	  name: 'Demo subroutes :: Customer routes'
	  uriPattern: 'demo/clients/{@action}(.{@format})'
	  defaults:
	    '@package': Demo
	    '@format':  html
	    '@controller': Customer

	--
	  name: 'Demo subroutes :: Standard routes'
	  uriPattern: 'demo/{@action}(.{@format})'
	  defaults:
	    '@package': Demo
	    '@format':  html
	    '@controller': Standard

	--
	  name: 'Demo subroutes :: Fallback'
	  uriPattern: 'demo(.{@format})'
	  defaults:
	    '@package': Demo
	    '@format':  html
	    '@controller': Standard
	    '@action':     index

You can even reference multiple subroutes from one route - that will create one route for
all possible combinations.

.. tip:: You can use the following command-line command to list all routes which are currently active:

	.. code-block:: bash

		$ ./flow3 routing:list

		Currently registered routes:
		typo3/login(/{@action}.{@format})         TYPO3 :: Authentication
		typo3/logout                              TYPO3 :: Logout
		typo3/setup(/{@action})                   TYPO3 :: Setup
		typo3                                     TYPO3 :: Backend Overview
		typo3/content/{@action}                   TYPO3 :: Backend - Content Module
		{node}.html/{type}                        TYPO3 :: Frontend content with format and type
		{node}.html                               TYPO3 :: Frontend content with (HTML) format
		({node})                                  TYPO3 :: Frontend content without a specified format
		                                          TYPO3 :: Fallback rule – for when no site has been defined yet
