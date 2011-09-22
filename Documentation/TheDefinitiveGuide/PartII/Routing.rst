=======
Routing
=======

.. sectionauthor:: Robert Lemke <robert@typo3.org>

Although the basic functions like creating or updating a post work well
already, the URIs still have a little blemish. The index of posts can only be
reached by the cumbersome address http://dev.tutorial.local/typo3.blog/post
and the URL for editing a post refers to the post's UUID instead of the
human-readable identifier.

FLOW3's routing mechanism allows for beautifying these URIs by simple but
powerful configuration options.

Post Index Route
================

Our first task is to simplify accessing the list of posts. For that you need to
edit a file called *Routes.yaml* in the global *Configurations/* directory
(located at the same level like the *Data* and *Packages* directories).
This file already contains a few routes which we ignore for the time being.

Please insert the following configuration at the top of the file (before the
TYPO3CR route) and make sure that you use spaces exactly like in the example
(remember, spaces have a meaning in YAML files and tabs are not allowed):

.. code-block:: yaml

	--
	  name: 'Post index'
	  uriPattern:    '(posts)'
	  defaults:
		'@package':    'TYPO3.Blog'
		'@controller': 'Post'
		'@action':     'index'
		'@format':     'html'

This configuration adds a new route to the list of routes (``--`` creates a new
list item). The route becomes active if a requests matches the pattern defined
by the ``uriPattern``. In this example empty URIs
(i.e. http://dev.tutorial.local/) and the URI http://dev.tutorial.local/posts
would match because the round brackets make the ``posts`` string optional.

If the URI matches, the route's default values for package, controller action
and format are set and the request dispatcher will choose the right
controller accordingly.

Try calling http://dev.tutorial.local/ and http://dev.tutorial.local/posts now –
you should in both cases see the list of posts produced by the
``PostController``'s ``indexAction``.

Composite Routes
================

As you can imagine, you rarely define only one route per package and storing
all routes in one file can easily become confusing. To keep the global
*Routes.yaml* clean you may define sub routes which include - if their own URI
pattern matches - further routes provided by your package.

The *FLOW3* sub route configuration for example includes further routes if
the URI path starts with the string '``TYPO3CR``'. Only the URI part contained
in the less-than and greater-than signs will be passed to the sub routes:

.. code-block:: yaml

	##
	# FLOW3 subroutes
	#

	-
	  name: 'FLOW3'
	  uriPattern: '<FLOW3Subroutes>'
	  defaults:
	    '@format': 'html'
	  subRoutes:
	    FLOW3Subroutes:
	      package: TYPO3.FLOW3

Let's define a similar configuration for the *Blog* package. Please replace
the YAML code you just inserted (the blog index route) by the following sub
route configuration:

.. code-block:: yaml

	##
	# Blog subroutes

	--
	  name: 'Blog'
	  uriPattern: '<BlogSubroutes>'
	  subRoutes:
		BlogSubroutes:
		  package: TYPO3.Blog

For this to work you need to create a new *Routes.yaml* file in the
*Configuration* folder of your *Blog* package
(*Packages/Application/TYPO3.Blog/Configuration/Routes.yaml*) and paste the
route you already created:

.. code-block:: yaml

	#                                                                        #
	# Routes configuration for the Blog package                              #
	#                                                                        #

	--
	  name: 'Post index'
	  uriPattern:    '(posts)'
	  defaults:
		'@package':    'TYOPO3.Blog'
		'@controller': 'Post'
		'@action':     'index'
		'@format':     'html'

An Action Route
===============

The URI pointing to the ``newAction`` is still http://dev.tutorial.local/typo3.blog/post/new
so let's beautify the action URIs as well by inserting a new route before the
'``Blogs``' route:

.. code-block:: yaml

	--
	  name: 'Post actions 1'
	  uriPattern:    'posts/{@action}'
	  defaults:
		'@package':    'TYPO3.Blog'
		'@controller': 'Post'
		'@format':     'html'

Reload the post index and check out the new URI of the ``createAction`` - it's
a bit shorter now:

.. image:: /Images/GettingStarted/PostActionRoute1URI.png

However, the edit link still looks it bit ugly:

	``http://dev.tutorial.local/post/edit?post%5B__identity%5D=229e2b23-b6f3-4422-8b7a-efb196dbc88b``

For getting rid of this long identifier we need the help of a Route
Part Handler.

Route Part Handlers
===================

Route Part Handlers are classes which allow for custom conversion of arguments
into URI parts and back. Our goal is to produce an URI like

	``http://dev.tutorial.local/post/2010/01/18/post-title/edit``

and use this as our edit link.

.. note::
	At the time of this writing it is necessary to implement a custom route
	part handler for solving this task. However, we do plan to provide a generic
	route part handler which can be used at least for the simple cases like the
	one we're looking at now.

A route part handler must be able to

	-	convert a list (array) of arguments for a certain sub part into a URI
		part (resolve)
	-	convert a URI part back into a list (array) of arguments (match)

Please create a new folder *TYPO3.Blog/Classes/RoutePartHandlers/* and a new
file called *PostRoutePartHandler.php*. Then copy & paste the following code::

	<?php
	namespace TYPO3\Blog\RoutePartHandlers;

	/**
	 * post route part handler
	 *
	 * @scope prototype
	 */
	class PostRoutePartHandler extends \TYPO3\FLOW3\MVC\Web\Routing\DynamicRoutePart {

		/**
		 * Splits the given value into the date and title of the post and sets this
		 * value to an identity array accordingly.
		 *
		 * @param string $value The value (ie. part of the request path) to match. This string is rendered by findValueToMatch()
		 * @return boolean TRUE if the request path formally matched
		 */
		protected function matchValue($value) {
			if (!parent::matchValue($value)) {
				return FALSE;
			}
			$matches = array();
			preg_match('/^([0-9]{4})\/([0-9]{2})\/([0-9]{2})\/([a-zA-Z0-9\-]+)/', $value, $matches);
			$this->value = array(
				'__identity' => array(
					'title' => str_replace('-', ' ', $matches[4])
				)
			);
			return TRUE;
		}

		/**
		 * Checks if the remaining request path starts with the path signature of a post, which
		 * is: YYYY/MM/DD/TITLE eg. 2009/03/09/my-first-blog-entry
		 *
		 * If the request path matches this pattern, the matching part is returned as the "value
		 * to match" for further processing in matchValue(). The remaining part of the requestPath
		 * (eg. the format ".html") is ignored.
		 *
		 * @param string $requestPath The request path acting as the subject for matching in this Route Part
		 * @return string The post identifying part of the request path or an empty string if it doesn't match
		 */
		protected function findValueToMatch($requestPath) {
			$matches = array();
			preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}\/[a-z0-9\-]+/', $requestPath, $matches);
			return (count($matches) === 1) ? current($matches) : '';
		}

		/**
		 * Resolves the name of the post
		 *
		 * @param \TYPO3\Blog\Domain\Model\Post $value The Post object
		 * @return boolean TRUE if the post could be resolved and stored in $this->value, otherwise FALSE.
		 */
		protected function resolveValue($value) {
			if (!$value instanceof \TYPO3\Blog\Domain\Model\Post) return FALSE;
			$this->value = $value->getDate()->format('Y/m/d/');
			$this->value .= strtolower(str_replace(' ', '-', $value->getTitle()));
			return TRUE;
		}
	}
	?>

The method ``resolveValue`` will later receive a ``Post`` object which its
supposed to convert into a string suitable for being used in the URI.
What this ``resolveValue`` implementation does is use the post's date and title
as the URI path segment.

The ``matchValue`` method on the other hand receives a part of the URI path
which has been requested by the user. This part will be the posts's date and
title as it was found in the URI. For FLOW3 being able to recognize that the
route part value needs to be converted into an object, a special ``__identity``
array needs to be created which in the end contains the ``Post`` properties.

Don't worry if you don't understand this mechanism on the first glance, it
really is an advanced topic. But we want beautified URIs from the beginning,
don't we?

Now that you have created a custom route part handler we need to include it
into our routes configuration by adding another route at the top of the file:

.. code-block:: yaml

	--
	  name: 'Post actions 2'
	  uriPattern:    'posts/{post}/{@action}'
	  defaults:
		'@package':    'TYPO3.Blog'
		'@controller': 'Post'
		'@format':     'html'
	  routeParts:
		post:
		  handler: TYPO3\Blog\RoutePartHandlers\PostRoutePartHandler

The "``Post actions 2``" route now handles all actions where a post needs to
be specified (i.e. show, edit, update and delete). In case the requested URI is
``http://dev.tutorial.local/post/2010/01/18/post-title/edit``, the post route part
handler's method ``matchValue`` will be called with the parameter
``2010/01/18/post-title`` which then will be converted to the ``Post`` object
with just that identifier.

Finally, now that you copied and pasted so much code, you should try out the
new routing setup ...

More on Routing
===============

The more an application grows, the more complex routing can become and
sometimes you'll wonder which route FLOW3 eventually chose. One way to get
this information is looking at the log file which is by default
located in *Data/Logs/System_Development.log*:

.. image:: /Images/GettingStarted/RoutingLogTail.png

More information on routing can be found in the :doc:`The Definitive Guide <../PartIII/Routing>`.
