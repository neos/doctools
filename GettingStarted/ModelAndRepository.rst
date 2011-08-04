====================
Model and Repository
====================

Usually this would now be the time to write a database schema which contains
table definitions and lays out relations between the different tables. But
FLOW3 doesn't deal with tables. You won't even access a database manually nor
will you write SQL. The very best is if you completely forget about tables and
databases and think only in terms of objects.

.. sidebar:: Code Examples

    The following sections contain a lot of code which we'll go through step
    by step. You may, but don't have to copy and paste the code to follow``
    the examples.
    If you're lost or just like to peek at the final code, go to the resources
    folder of the ``GettingStarted`` package: In *Private/CheatSheet/* you'll
    find all files mentioned in this tutorial (in fact *CheatSheet* contains
    the whole package ``Blog`` which you'll develop yourself step by step).

Domain models are really the heart of your application and therefore it is
vital that this layer stays clean and legible. In a FLOW3 application a model
is just a plain old PHP object  [#]_\ . There's no need to write a schema
definition, subclass a special base model or implement a required interface.
All FLOW3 requires from you as a specification for a model is a proper
documented PHP class containing properties.

Before you continue first create the directory for your domain models:

console::

	myhost:tutorial johndoe$ mkdir -p Packages/Application/TYPO3.Blog/Classes/Domain/Model

The directory structure and filenames follow the conventions of our
`Coding Guidelines <http://flow3.typo3.org/documentation/coding-guidelines/>`_ which 
basically means that the directories reflect the classes' namespace while the
filename is identical to the class name.

.. tip::
	Namespaces have been introduced in PHP 5.3. If you're unfamiliar with its
	funny backslash syntax you might want to have a look at the 
	`PHP manual <http://php.net/manual/en/language.namespaces.php>`_\ .

Blog Model
==========

The code for your ``Blog`` model (*.../TYPO3.Blog/Classes/Domain/Model/Blog.php)*
might look like the following:

PHP Code::

	<?php
	namespace TYPO3\Blog\Domain\Model;
	
	/**
	 * A blog
	 *
	 * @scope prototype
	 * @entity
	 */
	class Blog {
		
		/**
		 * The blog's title.
		 *
		 * @var string
		 */
		protected $title = '';
		
		/**
		 * A short description of the blog
		 *
		 * @var string
		 */
		protected $description = '';
		
		/**
		 * The posts contained in this blog
		 *
		 * @var \Doctrine\Common\Collections\ArrayCollection<\TYPO3\Blog\Domain\Model\Post>
		 */
		protected $posts;
		
		/**
		 * Constructs a new Blog
		 *
		 */
		public function __construct() {
			$this->posts = new \Doctrine\Common\Collections\ArrayCollection();
		}
		
		/**
		 * Sets this blog's title
		 *
		 * @param string $title The blog's title
		 * @return void
		 */
		public function setTitle($title) {
			$this->title = $title;
		}
		
		/**
		 * Returns the blog's title
		 *
		 * @return string The blog's title
		 */
		public function getTitle() {
			return $this->title;
		}
		
		/**
		 * Sets the description for the blog
		 *
		 * @param string $description The blog description or "tag line"
		 * @return void
		 */
		public function setDescription($description) {
			$this->description = $description;
		}
		
		/**
		 * Returns the description
		 *
		 * @return string The blog description
		 */
		public function getDescription() {
			return $this->description;
		}
		
		/**
		 * Adds a post to this blog
		 *
		 * @param \TYPO3\Blog\Domain\Model\Post $post
		 * @return void
		 */
		public function addPost(\TYPO3\Blog\Domain\Model\Post $post) {
			$post->setBlog($this);
			$this->posts->add($post);
		}
		
		/**
		 * Returns all posts in this blog
		 *
		 * @return \Doctrine\Common\Collections\ArrayCollection<\TYPO3\Blog\Domain\Model\Post> The posts of this blog
		 */
		public function getPosts() {
			return clone $this->posts;
		}
	
	}
	?>

As you can see there's nothing really fancy in it, the class mostly consists of
getters and setters. Let's take a closer look at the model line-by-line:

PHP Code::
	namespace TYPO3\Blog\Domain\Model;
	
This namespace declaration must be the very first code in your file.

PHP Code::

	/**
	 * A blog
	 *
	 * @scope prototype
	 * @entity
	 */
	class Blog {

On the first glance this looks like a regular comment block, but it's not. This
comment contains two **annotations** which are an important building block in 
FLOW3's configuration mechanism.

The ``@scope`` annotation defines the object scope. By default only one global
instance exists of each class – this is called the **singleton scope**. If we
want to allow multiple instances at a time (and potentially there are multiple
``Blog`` objects) we need to annotate the class with ``@scope prototype``.
Don't worry about this now, you'll soon learn more about scopes and object
management in general.

The second annotation marks this class as an ``@entity``. This is an important
piece of information for the persistence framework because it declares that

	- 	this model is an **entity** according to the concepts of Domain-Driven
		Design
	- 	instances of this class can be persisted (i.e. stored in the database)
	-	According to DDD, an entity is an object which has an identity, that
		is even if two objects with the same values exist, their identity
		matters.

The model's properties are implemented as regular class properties:

PHP Code::

	/*
	 * The blog's title.
	 *
	 * @var string
	 */
	protected $title = '';
	
	/**
	 * A short description of the blog
	 *
	 * @var string
	 */
	protected $description = '';
	
	/**
	 * The posts contained in this blog
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection<\TYPO3\Blog\Domain\Model\Post>
	 * @OneToMany(mappedBy="blog",cascade={"all"})
	 */
	protected $posts;

Each property comes with a ``@var`` annotation which declares its type. Any
type is fine, be it simple types like ``string``, ``integer``, ``boolean`` 
or classes like ``\DateTime``, ``\TYPO3\Foo\Domain\Model\Bar`` or 
``\ArrayObject``. Regarding the type, the ``@var`` annotation of the ``$posts``
property differs a bit from the remaining comments. This property holds a list
of ``Post`` objects contained by this blog – in fact this could easily have
been an array:

PHP Code::

	/**
	 * The posts contained in this blog
	 *
	 * @var array<\TYPO3\Blog\Domain\Model\Post>
	 */
	protected $posts = array();

However, an array would allow ``$posts`` to contain the same post multiple
times. We therefore use an ``Doctrine\Common\Collections\ArrayCollection``
which guarantees the uniqueness of each post attached to it.

The class name bracketed by the less-than and greater-than signs gives an
important hint on the content of the array or object storage. There are a few
situations in which FLOW3 relies on this information.

The remaining code shouldn't hold any surprises - it only serves for setting
and retrieving the blog's properties. This again, is no requirement by 
FLOW3 - if you don't want to expose your properties it's fine to not define any
setters or getters at all. The persistence framework uses other ways to access
the properties' values ...

Blog Repository
===============

According to our earlier reasonings, you need a repository for storing the blog:

.. figure:: ../Images/GettingStarted/DomainModel-3.png

	Blog Repository and Blog


A repository acts as the bridge between the holy lands of business logic
(domain models) and the dirty underground of infrastructure (data storage).
This is the only place where queries to the persistence framework take place -
you never want to have those in your domain models.

First create the directory for your repositories:

console::

	myhost:tutorial johndoe$ mkdir -p Packages/Application/TYPO3.Blog/Classes/Domain/Repository

Implementing a vanilla repository for blogs is as easy as this
(*.../TYPO3.Blog/Classes/Domain/Repository/BlogRepository.php*):

PHP Code::

	<?php
	namespace TYPO3\Blog\Domain\Repository;
	
	/**
	 * A repository for Blogs
	 */
	class BlogRepository extends \TYPO3\FLOW3\Persistence\Repository {
	}
	?>

As you see there's no code you need to write for the standard cases because
the base repository already comes with methods like ``add``, ``remove``, 
``findAll``, ``findBy*`` and ``findOneBy*`` [#]_ methods.

Remember that a repository can only store one kind of an object, in this case
blogs. The type is derived from the repository name: because you named this
repository ``BlogRepository`` FLOW3 assumes that it's supposed to store 
``Blog`` objects.

-----

.. [#]
		We love to call them POPOs, similar to POJOs 
		http://en.wikipedia.org/wiki/Plain_Old_Java_Object
.. [#]
		``findBy*`` and ``findOneBy*`` are magic methods provided by the base
		repository which allow you to find objects by properties. The
		``BlogRepository`` for example would allow you to call magic methods
		like ``findByDescription('foo')`` or ``findOneByTitle('bar')``.