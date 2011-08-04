==========
Controller
==========

Now that we have the first model and repository in place we can move forward to
creating our first controller.

.. note::
	You will need the ``Post`` model and the ``PostRepository`` to make the
	following examples work. Make sure to copy them from the cheat sheet
	directory if you want to keep on trying the tutorial code alongside.


Setup Controller
================

The ``SetupController`` will be in charge of creating a ``Blog`` object,
setting a title and description and storing it in the ``BlogRepository``.
The kickstarter created a very basic setup controller containing only one
action, the ``indexAction``. Let's create and store a 
new blog once the index action is called:

PHP Code::

	<?php
	namespace TYPO3\Blog\Controller;
	
	// ...
	
	class SetupController extends \TYPO3\FLOW3\MVC\Controller\ActionController {
	
		/**
		 * @inject
		 * @var \TYPO3\Blog\Domain\Repository\BlogRepository
		 */
		protected $blogRepository;
	
		/**
		 * Sets up a fresh blog and creates a sample post.
		 *
		 * @return void
		 */
		public function indexAction() {
			$this->blogRepository->removeAll();
	
			$blog = new \TYPO3\Blog\Domain\Model\Blog();
			$blog->setTitle('My Blog');
			$blog->setDescription('A blog about Foo, Bar and Baz.');
			$this->blogRepository->add($blog);
	
			$post = new \TYPO3\Blog\Domain\Model\Post();
			$post->setAuthor('John Doe');
			$post->setTitle('Example Post');
			$post->setContent('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.');
	
			$blog->addPost($post);
	
			return 'Successfully created a blog';
		}
	}
	?>

You can probably figure out easily what the ``indexAction`` does – it empties
the ``BlogRepository``, creates a new ``Blog`` object and adds it to the
``BlogRepository``. In addition a sample blog post is created and added to the
blog. Note that if you had ommitted the line

``$this->blogRepository->add($blog);``

the blog and the post would have been created in memory but not persisted to
the database.

Using the blog repository sounds plausible, but where do you get the 
``blogRepository`` from?

PHP Code::

	/**
	 * @var \TYPO3\Blog\Domain\Repository\BlogRepository
	 * @inject
	 */
	protected $blogRepository;

The property declaration for ``$blogRepository`` is marked with an ``@inject``
annotation. This signals to the object framework: I need the blog repository
here, please make sure it's stored in this member variable. In effect FLOW3
will inject the blog repository into the ``$blogRepository`` property right
after your controller has been instantiated. And because the blog repository's
scope is *singleton* [#]_\ , the framework will always inject the same instance
of the repository.

There's a lot more to discover about **Dependency Injection** and we recommend
that you read the whole chapter about objects in the `FLOW3 Reference <http://flow3.typo3.org/documentation/manuals/>`_
once you start with your own coding.

To create the required database tables we now use the command line support. 
The first command is to set up the basic tables, while the second command is
used to generate the tables for our package:

console::

	myhost:tutorial johndoe$ ./flow3 typo3.flow3:doctrine:migrate
	myhost:tutorial johndoe$ ./flow3 typo3.flow3:doctrine:update

Try out the ``SetupController`` by accessing 
http://dev.tutorial.local/typo3.blog/setup/index. If all went right (and you
copied the needed files, e.g. the ``Post`` model, from the *CheatSheet* folder
to the right places), you should see the *Successfully created a blog* message
on your screen. In order to find this blog again, we add a method ``findActive``
to the ``BlogRepository``:

PHP Code::

	class BlogRepository extends \TYPO3\FLOW3\Persistence\Repository {
	
		/**
		 * Finds the active blog.
		 *
		 * As of now only one Blog is supported anyway so we just assume that only one
		 * Blog object resides in the Blog Repository.
		 *
		 * @return \TYPO3\Blog\Domain\Model\Blog The active blog or FALSE if none exists
		 */
		public function findActive() {
			$query = $this->createQuery();
			$result = $query->setLimit(1)->execute();
			return $result->getFirst();
		}
	}


This is all we need for moving on to something more visible: the blog posts.


Basic Post Controller
=====================

PHP Code::

	<?php
	namespace TYPO3\Blog\Controller;
	
	// ...
	
	class PostController extends \TYPO3\FLOW3\MVC\Controller\ActionController {
	
		/**
		 * @var \TYPO3\Blog\Domain\Repository\BlogRepository
		 * @inject
		 */
		protected $blogRepository;
	
		/**
		 * Index action
		 *
		 * @return string HTML code
		 */
		public function indexAction() {
			$blog = $this->blogRepository->findActive();
			$output = '
				&lt;h1>Posts of "' . $blog->getTitle() . '"&lt;/h1>
				&lt;ol>';
	
			foreach ($blog->getPosts() as $post) {
				$output .= '&lt;li>' . $post->getTitle() . '&lt;/li>';
			}
	
			$output .= '&lt;/ol>';
	
			return $output;
		}
	}
	?>

The ``indexAction`` retrieves the active blog from the ``BlogRepository`` and
outputs the blog's title and post titles [#]_\ . A quick look 
at http://dev.tutorial.local/typo3.blog/post [#]_ confirms that the
``SetupController`` has indeed created the blog and post:

.. figure:: ../Images/GettingStarted/MyFirstBlog.png

	Output of the indexAction

Create Action
=============

In the ``SetupController`` we have seen how a new blog and a post can be
created and filled with some hardcoded values. At least the posts should,
however, be filled with values provided by the blog author, so we need to pass
the new post as an argument to a ``createAction`` in the ``PostController``:

PHP Code::

	/**
	 * @var \TYPO3\Blog\Domain\Model\Blog
	 */
	protected $blog;

	// ...

	/**
	 * Initializes any action.
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->blog = $this->blogRepository->findActive();
		if ($this->blog === FALSE) {
			$this->redirect('index', 'Setup');
		}
	}
	/**
	 * Creates a new post
	 *
	 * @param \TYPO3\Blog\Domain\Model\Post $post A fresh Post object which has not yet been added to the repository
	 * @return void
	 */
	public function createAction(\TYPO3\Blog\Domain\Model\Post $post) {
		$this->blog->addPost($post);
		$this->flashMessageContainer->add('Your new post was created.');
		$this->redirect('index');
	}


.. tip::
	The ``initializeAction`` method is called before any other action method
	is called. We use it for retrieving the active blog and store it for
	later use.

The ``createAction`` expects a parameter ``$post`` which is the ``Post`` object
to be persisted. The code is quite straight-forward: add the post to the blog,
add a message to some flash message stack and redirect to the index action. 
Try calling the ``createAction`` now by accessing
http://dev.tutorial.local/typo3.blog/post/create:

.. image:: ../Images/GettingStarted/CreateActionWithoutArgument.png

FLOW3 analyzed the new method signature and automatically registered ``$post``
as a required argument for ``createAction``. Because no such argument was
passed to the action, the controller exits with an error.

So, how do you create a new post? You need to create some HTML form which
allows you to enter the post details and then submits the information to the
``createAction``. But you don't want the controller rendering such a
form – this is clearly a task for the view!

-----

.. [#]	Remember, *singleton* is the default object scope and because the
		``BlogRepository`` does not contain a ``@scope`` annotation, it has the
		default scope.
.. [#]	Don't worry, the action won't stay like this – of course later we'll
		move all HTML rendering code to a dedicated view.
.. [#]	The first *blog* stands for the package *Blog* and *post* specifies the
		controller *PostController*.