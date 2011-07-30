===========================
Aspect-Oriented Programming
===========================

.. ============================================
.. Meta-Information for this chapter
.. ---------------------------------
.. Author: Robert Lemke
.. Converted to ReST by: Christian Müller
.. Updated for 1.0 beta1: NO
.. TODOs: none
.. ============================================

Introduction
============

Aspect-Oriented Programming (AOP) is a programming paradigm which complements
Object-Oriented Programming (OOP) by separating *concerns* of a software
application to improve modularization. The separation of concerns (SoC) aims for
making a software easier to maintain by grouping features and behavior into
manageable parts which all have a specific purpose and business to take care of.

OOP already allows for modularizing concerns into distinct methods, classes and
packages. However, some concerns are difficult to place as they cross the
boundaries of classes and even packages. One example for such a *cross-cutting
concern* is security: Although the main purpose of a Forum package is to display
and manage posts of a forum, it has to implement some kind of security to assert
that only moderators can approve or delete posts. And many more packages need a
similar functionality for protect the creation, deletion and update of records.
AOP enables you to move the security (or any other) aspect into its own package
and leave the other objects with clear responsibilities, probably not
implementing any security themselves.

Aspect-Oriented Programming has been around in other programming languages for
quite some time now and sophisticated solutions taking advantage of AOP exist.
FLOW3's AOP framework allows you to use of the most popular AOP techniques in
your own PHP application. In contrast to other approaches it doesn't require any
special PHP extensions, additional compile steps or modification of the target
code – and it's a breeze to configure.

.. tip::
	In case you are unsure about some terms used in this introduction or later
	in this chapter, it's a good idea looking them up (for example at
	Wikipedia_). Don't think that you're the only one who has never heard of a
	*Pointcut* or *SoC* [#]_ – we had a hard time learning these too. However,
	it's worth the hassle, as a common vocabulary improves the communication
	between developers a lot.
.. _Wikipedia: http://en.wikipedia.org/

AOP concepts and terminology
----------------------------

Let's stay with the example of a Forum for a while. The classes of the forum
don't implement security themselves, but somehow we have to make sure that
whenever a method `deletePost()`` is called, a security check takes place. The
class containing the delete method is called the target class. We have a new
*aspect* called "security" which we'd like to *weave* into that class. Whenever
the method `deletePost()`` is called, a *method interceptor* defined by an
*around advice* will intercept the target method and only proceed if the
operation is allowed in the current security context.

At the first (and the second, third, ...) glance, the terms used in the AOP
context are not really intuitive. But, similar to most of the other AOP
frameworks, we better stick to them, to keep a common language between
developers. Here they are:

Aspect
	An aspect is the part of the application which cross-cuts the core concerns
	of multiple objects. In FLOW3, aspects are implemented as regular classes
	which are tagged by the @aspect annotation. The methods of an aspect class
	represent advices, the properties act as an anchor for introductions.
	
Join point
	A join point is a point in the flow of a program. Examples are the execution
	of a method or the throw of an exception. In FLOW3, join points are
	represented by the ``\F3\FLOW3\AOPJoinPoint`` object which contains more
	information about the circumstances like name of the called method, the
	passed arguments or type of the exception thrown. A join point is an event
	which occurs during the program flow, not a definition which defines that
	point.
	
Advice
	An advice is the action taken by an aspect at a particular join point.
	Advices are implemented as methods of the aspect class. These methods are
	executed before and / or after the join point is reached.
	
Pointcut
	The pointcut defines a set of join points which need to be matched before
	running an advice. The pointcut is configured by a *pointcut expression*
	which defines when and where an advice should be executed. FLOW3 uses
	methods in an aspect class as anchors for pointcut declarations.
	
Pointcut expression
	A poincut expression is the condition under which a joinpoint should match.
	It may, for example, define that joinpoints only match on the execution of a
	(target-) method with a certain name. Pointcut expressions are used in
	pointcut- and advice declarations.
	
Target
	A class or method being adviced by one or more aspects is referred to as a
	target class /-method.
	
Introduction
	An introduction redeclares the target class to implement an additional
	interface. By declaring an introduction it is possible to introduce new
	interfaces and an implementation of the required methods without touching
	the code of the original class.
	
The following terms are related to advices:

Before advice
	A before advice is executed before the target method is being called, but
	cannot prevent the target method from being executed.

After returning advice
	An after returning advice is executed after returning from the target
	method. The result of the target method invocation is available to the after
	returning advice, but it can't change it. If the target method throws an
	exception, the after returning advice is not executed.
	
After throwing advice
	An after throwing advice is only executed if the target method throwed an
	exception. The after throwing advice may fetch the exception type from the
	join point object.
	
After advice
	An after advice is executed after the target method has been called, no
	matter if an exception was thrown or not.
	
Around advice
	An around advice is wrapped around the execution of the target method. It
	may execute code before and after the invocation of the target method and
	may ultimately prevent the original method from being executed at all. An
	around advice is also responsible for calling other around advices at the
	same join point and returning either the original or a modified result for
	the target method.
	
Advice chain
	If more than one around advice exists for a join point, they are called in
	an onion-like advice chain: The first around advice probably executes some
	before-code, then calls the second around advice which calls the target
	method. The target method returns a result which can be modified by the
	second around advice, is returned to the first around advice which finally
	returns the result to the initiator of the method call. Any around advice
	may decide to proceed or break the chain and modify results if necessary.
	
FLOW3 AOP concepts
------------------

Aspect-Oriented Programming was, of course, not invented by us [#]_. Since the
initial release of the concept, dozens of implementations for various
programming languages evolved. Although a few PHP-based AOP frameworks do exist,
they followed concepts which did not match the goals of FLOW3 (to provide a
powerful, yet developer-friendly solution) when the development of TYPO3 5.0
began. We therefore decided to create a sophisticated but pragmatic
implementation which adopts the concepts of AOP but takes PHP's specialties and
the requirements of typical FLOW3 applications into account. In a few cases this
even lead to new features or simplifications because they were easier to
implement in PHP compared to Java.

FLOW3 pragmatically implements a reduced subset of AOP, which satisfies most
needs of web applications. The join point model allows for intercepting method
executions but provides no special support for advising field access [#]_. For
the sake of simplicity and performance, pointcuts don't allow criteria which
have to be evaluated at runtime (such as matching argument values of a method)
and pointcut expressions are based on well-known regular expressions instead of
requiring the knowledge of a dedicated expression language. Pointcut filters and
join point types are modularized and can be extended if more advanced
requirements should arise in the future.

Implementation overview
-----------------------

FLOW3's AOP framework does not require a pre-processor or an aspect-aware PHP
interpreter to weave in advices. It is implemented and based on pure PHP and
doesn't need any specific PHP extension. However, it does require the Object
Manager to fulfill its task.


FLOW3 uses PHP's reflection capabilities to analyze declarations of aspects,
pointcuts and advices and implements method interceptors as a dynamic proxy. In
accordance to the GoF patterns [#]_, the proxy classes act as a placeholders for
the target object. They are true subclasses of the original and override adviced
methods by implementing a interceptor method. The proxy classes are generated
automatically by the AOP framework and cached for further use. If a class has
been adviced by some aspect, the Object Manager will only deliver instances of
the proxy class instead of the original.

The approach of storing generated proxy classes in files provides the whole
advantage of dynamic weaving with a minimum performance hit. Debugging of
proxied classes is still easy as they truly exist in real files.

Aspects
=======

Aspects are abstract containers which accommodate pointcut-, introduction- and
advice declarations. In most frameworks, including FLOW3, aspects are defined as
plain classes which are tagged (annotated) as an aspect. The following example
shows the definition of a hypothetical ``FooSecurity`` aspect:




*Example: Declaration of an aspect*::

	namespace F3\MySecurityPackage;

	/**
	 * An aspect implementing security for Foo
	 *
	 * @author	John Doe <john@typo3.org>
	 * @aspect
	 */
	class FooSecurityAspect {
	
	}
	
As you can see, ``\F3\MySecurityPackage\FooSecurityAspect`` is just a regular
PHP class which may (actually must) contain methods and properties. What it
makes it an aspect is solely the @aspect annotation mentioned in the class
comment. The AOP framework recognizes this tag and registers the class as an
aspect.

.. Note::
	A void aspect class doesn't make any sense and if you try to run the above
	example, the AOP framework will throw an exception complaining that no
	advice, introduction or pointcut has been defined.
	
Pointcuts
=========

If we want to add security to foo, we need a method which carries out the security checks and a definition where and when this method should be executed. The method is an advice which we're going to declare in a later section, the “where and when” is defined by a pointcut expression in a pointcut declaration.

You can either define the pointcut in the advice declaration or set up named pointcuts to help clarify their use.

A named pointcut is represented by a method of an aspect class. It contains two pieces of information: The pointcut name, defined by the methodname, and the poincut expression, declared by an annotation. The following pointcut will match the execution of methods whose name starts with “delete”, no matter in which class they are defined:



*Example: Declaration of a named pointcut*::

	/**
	 * A pointcut which matches all methods whose name starts with "delete".
	 *
	 * @pointcut method(.*->delete.*())
	 * @author John Doe <john@typo3.org>
	 */
	public function deleteMethods() {}
	
Pointcut expressions
--------------------

As already mentioned, the pointcut expression configures the filters which are
used to match against join points. It is comparable to an if condition in PHP:
Only if the whole condition evaluates to TRUE, the statement is executed -
otherwise it will be just ignored. If a pointcut expression evaluates to TRUE,
the pointcut matches and advices which refer to this poincut become active.

.. Note::
	The AOP framework AspectJ provides a complete pointcut language with dozens
	of pointcut types and expression constructs. FLOW3 makes do with only a
	small subset of that language, which we think already suffice for even
	complex enterprise applications. If you're interested in the original
	feature set, it doesn't hurt throwing a glance at the AspectJ Programming
	Guide.

Pointcut designators
--------------------

A pointcut expression always consists of two parts: The poincut designator and
its parameter(s). The following designators are supported by FLOW3:

method()
********

The ``method()`` designator matches on the execution of methods with a certain
name. The parameter specifies the class and method name, regular expressions can
be used for more flexibility [#]_. It follows the following scheme:

``method(public|protected ClassName->methodName())``

Specifying the visibility modifier (public or protected) is optional - if none
is specified, both visibilities will match. The class- and method name can be
specified as a regular expression. Here are some examples for matching method
executions:

*Example: method() pointcut designator*

-----

Matches all public methods in class \F3\MyPackage\MyObject:

``method(public F3\MyPackage\MyObject->.*())``

Matches all delete methods (even protected and private ones) in any class of the package MyPackage:

``method(F3\MyPackage\.*->delete.*())``

-----

.. Note::
	In other AOP frameworks, including AspectJ™ and Spring™, the method
	designator does not exist. They rather use a more fine grained approach with
	designators such as execution, call and cflow. As FLOW3 only supports
	matching to method execution join points anyway, we decided to simplify
	things by allowing only a more general method designator.

The ``method()`` designator also supports so called runtime evaluations, meaning you can specify values for the method's arguments. If those argument values do not match the adivce won't be executed. The following example should give you an idea how this works:

*Example runtime evaluations for the method() pointcut designator*

-----

``method(F3\MyPackage\MyClass->update(title == "FLOW3", overwrite == TRUE))``

-----

Besides the method arguments you can also access the properties of the current object or a global object like the party that is currently authenticated. A detailed description of the runtime evaluations possibilites is described below in the section about the evaluate() pointcut filter.

class()
*******

The ``class()`` designator matches on the execution of methods defined in a class with a certain name. The parameter specifies the class name, again regular expressions are allowed here. The ``class()`` designator follows this simple scheme:

``class(classname)``

*Example class() pointcut designator*

-----

Matches all methods in class F3\MyPackage\MyObject:

``class(F3\MyPackage\MyObject)``

-----

within()
********

The ``within()`` designator matches on the execution of methods defined in a class of a certain type. A type matches if the class is a subclass of or implements an interface of the given name. The ``within()`` designator has this simple syntax:

``within(type)``

*Example: within() pointcut designator*

-----

Matches all methods in classes which implement the logger interface:

``within(\F3\FLOW3\Log\LoggerInterface)``

Matches all methods in classes which are part of the Foo layer:

``within(\F3\FLOW3\FooLayerInterface)``

------

classTaggedWith()
*****************

The ``classTaggedWith()`` designator matches on classes which are tagged with a certain annotation. As with class and method names, a regular expression can be used to describe the matching tags. The syntax of this designator is as follows:

``classTaggedWith(tag)``

*Example: classTaggedWith() pointcut designator*

-----

Matches all classes which are tagged with an "@entity" annotation:

``classTaggedWith(entity)``

Matches all classes which are tagged with an annotation starting with "@cool":

``classTaggedWith(cool.*)``

-----

methodTaggedWith()
******************

The methodTaggedWith() designator matches on methods which are tagged with a certain annotation. As with other pointcut designators, a regular expression can be used to describe the matching tags. The syntax of this designator is as follows:

``methodTaggedWith(tag)``

*Example: methodTaggedWith() pointcut designator*

-----

Matches all method which are tagged with an "@special" annotation:

``methodTaggedWith(special)``

-----

setting()
*********

The setting() designator matches if the given configuration option is set to TRUE, or if an optional given comparison value equals to its configured value. You can use this designator as follows:

*Example: setting() pointcut designator*

-----

Matches if "my.configuration.option" is set to TRUE in the current execution context:

``setting(my.configuration.option)``

Matches if "my.configuration.option" is equal to "AOP is cool" in the current execution context: (Note: single and double quotes are allowed)

``setting(my.configuration.option = 'AOP is cool')``

-----

filter()
********

If the built-in filters don't suit your needs you can even define your own custom filters. All you need to do is create a class implementing the ``\F3\FLOW3\AOP\Pointcut\PointcutFilterInterface`` and develop your own logic for the ``matches()`` method. The custom filter can then be invoked by using the ``filter()`` designator:

``filter(CustomFilterObjectName)``

*Example: filter() pointcut designator*

-----

If the current method matches is determined by the custom filter:

``filter(F3\MyPackage\MyCustomPointcutFilter)``

-----

evaluate()
**********

The evaluate() designator is used to execute advices depending on constraints that have to be evaluated during runtime. This could be a specific value for a method argument (see the method() designator) or checking a certain property of the current object or accessing a global object like the currently authenticated party. In general you can access object properties by the . syntax and global objects are registered under the current. keyword. Here is an example for the possibilities:

*Example: evaluate() pointcut designator*

-----

Matches if the property name of the global party object (the currently authenticated party of the security framework) is equal to "Andi":

``evaluate(current.party.name == "Andi")``

Matches if the property someProperty of someObject which is a property of the current object (the object the advice will be executed in) is equals to the name of the current party:

``evaluate(this.someObject.someProperty == current.party.name)``

Matches if the property someProperty of the current object is equal to one of the values TRUE, "someString" or the address of the current party:

``evaluate(this.someProperty in (TRUE, "someString", current.party.address))``

Matches if the accounts array in the current party object contains the account stored in the myAccount property of the current object:

``evaluate(current.party.accounts contains this.myAccount)``

Matches if at least one of the entries in the first array exists in the second one:

``evaluate(current.party.accounts matches ('Administrator', 'Customer', 'User'))``

``evaluate(current.party.accounts matches this.accounts)``

------

.. tip::
	If you like you can enter more than one constraint in a single evaluate pointcut designator by separating them with a comma. The evaluate designator will only match, if all its conditions evaluated to TRUE.
	
.. note::
	Currently there is only the party object available under the current. namespace. In the future it should be possible to register arbitrary singletons to be available at this place.
	
Combining pointcut expressions
------------------------------

All pointcut expressions mentioned in previous sections can be combined into a whole expression, just like you may combine parts to an overall condition in an if construct. The supported operators are “&&”, “||” and “!” and they have the same meaning as in PHP. Nesting expressions with parentheses is not supported but you may refer to other pointcuts by specifying their full name (i.e. class- and method name). This final example shows how to combine and reuse pointcuts and ultimately build a hierarchy of pointcuts which can be used conveniently in advice declarations:

*Example: Combining pointcut expressions*::
	
	namespace F3\TestPackage;
	
	/**
	 * Fixture class for testing poincut definitions
	 *
	 * @aspect
	 */
	class PointcutTestingAspect {
	
		/**
		 * Pointcut which includes all method executions in
		 * pointcutTestingTargetClasses except those from Target
		 * Class number 3.
		 *
		 * @pointcut method(F3\TestPackage\PointcutTestingTargetClass.*->.*()) && ⏎
		  !method(F3\TestPackage\PointcutTestingTargetClass3->.*())
		 */
		public function pointcutTestingTargetClasses() {}
	
		/**
		 * Pointcut which consists of only the
		 * F3\TestPackage\OtherPointcutTestingTargetClass.
		 *
		 * @pointcut method(F3\TestPackage\OtherPointcutTestingTargetClass->.*())
		 */
		public function otherPointcutTestingTargetClass() {}
	
		/**
		 * A combination of both above pointcuts
		 *
		 * @pointcut F3\TestPackage\PointcutTestingAspect->pointcutTestingTargetClasses ⏎
		  || F3\TestPackage\PointcutTestingAspect->otherPointcutTestingTargetClass
		 * @author Robert Lemke <robert@typo3.org>
		 */
		public function bothPointcuts() {}
	
		/**
		 * A pointcut which matches all classes from the service layer
		 *
		 * @pointcut within(\F3\FLOW3\ServiceLayerInterface)
		 */
		public function serviceLayerClasses() {}
	
		/**
		 * A pointcut which matches any method from the BasicClass and all classes
		 * from the service layer
		 *
		 * @pointcut method(F3\TestPackage\Basic.*->.*()) || within(F3\FLOW3\Service.*)
		 */
		public function basicClassOrServiceLayerClasses() {}
	}

Declaring advice
================

With the aspect and pointcuts in place we are now ready to declare the advice. Remember that an advice is the actual action, the implementation of the concern you want to weave in to some target. Advices are implemented as interceptors which may run before and / or after the target method is called. Four advice types allow for these different kinds of interception: Before, After returning, After throwing and Around.

Other than being of a certain type, advices always come with a pointcut expression which defines the set of join points the advice applies for. The pointcut expression may, as we have seen earlier, refer to other named pointcuts.

Before advice
-------------

A before advice allows for executing code before the target method is invoked. However, the advice cannot prevent the target method from being executed, nor can it take influence on other before advices at the same join point.

*Example: Declaration of a before advice*::

	/**
	 * Before advice which is invoked before any method call within the News 
	 * package
	 *
	 * @before class(F3\News\.*->.*())
	 */
	public function myBeforeAdvice(\F3\FLOW3\AOP\JoinPointInterface ⏎
		$joinPoint) {
	}


After returning advice
----------------------

The after returning advice becomes active after the target method normally returns from execution (i.e. it doesn't throw an exception). After returning advices may read the result of the target method, but can't modify it.

*Example: Declaration of an after returning advice*::

	/**
	 * After returning advice
	 *
	 * @afterreturning method(public F3\News\FeedAgregator->[import|update].*()) ⏎
		  || F3\MyPackage\MyAspect->someOtherPointcut
	 */
	public function myAfterReturningAdvice(\F3\FLOW3\AOP\JoinPointInterface ⏎
		$joinPoint) {
	}


After throwing advice
---------------------

Similar to the “after returning” advice, the after throwing advice is invoked after method execution, but only if an exception was thrown.

*Example: Declaration of an after throwing advice*::

	/**
	 * After throwing advice
	 *
	 * @afterthrowing within(F3\News\ImportantLayer)
	 */
	public function myAfterThrowingAdvice(\F3\FLOW3\AOP\JoinPointInterface ⏎
		$joinPoint) {
	}


After advice
------------

The after advice is a combination of “after returning” and “after throwing”: These advices become active after method execution, no matter if an exception was thrown or not.

*Example: Declaration of an after advice*::

	/**
	 * After advice
	 *
	 * @after F3\MyPackage\MyAspect->justAPointcut
	 */
	public function myAfterAdvice(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {
	}


Around advice
-------------

Finally, the around advice takes total control over the target method and intercepts it completely. It may decide to call the original method or not and even modify the result of the target method or return a completely different one. Obviously the around advice is the most powerful and should only be used if the concern can't be implemented with the alternative advice types. You might already guess how an around advice is declared:

*Example: Declaration of an around advice*::

	/**
	 * Around advice
	 *
	 * @around F3\MyPackage\MyAspect->justAPointcut
	 */
	public function myAroundAdvice(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {
	}


Implementing advice
===================

The final step after declaring aspects, pointcuts and advices is to fill the advices with life. The implementation of an advice is located in the same method it has been declared. In that regard, an aspect class behaves like any other object in FLOW3 – you therefore can take advantage of dependency injection in case you need other objects to fulfill the task of your advice.

Accessing join points
---------------------

As you have seen in the previous section, advice methods always expect an argument of the type \F3\FLOW3\AOP\JoinPointInterface. This join point object contains all important information about the current join point. Methods like getClassName() or getMethodArguments() let the advice method classify the current context and enable you to implement advices in a way that they can be reused in different situations. For a full description of the join point object refer to the API documentation.

Advice chains
-------------

Around advices are a special advice type in that they have the power to completely intercept the target method. For any other advice type, the advice methods are called by the proxy class one after another. In case of the around advice, the methods form a chain where each link is responsible to pass over control to the next.

.. figure:: ../../Images/AOPFramework_AdviceChain.png

	Control flow of an advice chain
	
Examples
--------

Let's put our knowledge into practice and start with a simple example. First we would like to log each access to methods within certain package. The following code will just do that:

*Example: Simple logging with aspects*::
	
	namespace F3\MyPackage;
	
	/**
	 * A logging aspect
	 *
	 * @aspect
	 */
	class LoggingAspect {
	
		/**
		 * @var \F3\FLOW3\Log\LoggerInterface A logger implementation
		 */
		protected $logger;
	
		/**
		 * For logging we need a logger, which we will get injected automatically by 
		 * the Object Manager
		 *
		 * @param  \F3\FLOW3\Log\SystemLoggerInterface $logger The System Logger
		 * @return void
		 */
		public function injectSystemLogger(\F3\FLOW3\Log\SystemLoggerInterface ⏎
			$systemLogger) {
			$this->logger = $systemLogger;
		}
	
		/**
		 * Before advice, logs all access to methods of our package
		 *
		 * @param  \F3\FLOW3\AOP\JoinPointInterface $joinPoint: The current join point
		 * @return void
		 * @before method(F3\MyPackage\.*->.*())
		 */
		public function logMethodExecution(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {
			$logMessage = 'The method ' . $joinPoint->getMethodName() . ' in class ' . ⏎
				$joinPoint->getClassName() . ' has been called.';
			$this->logger->log($logMessage);
		}
	}


Note that we are using dependency injection for getting the system logger instance to stay independent from any specific logging implementation. We don't have to care about the kind of logger and where it comes from.

Finally an example for the implementation of an around advice: For a guest book, we want to reject the last name “Sarkosh” (because it should be “Skårhøj”), every time it is submitted. Admittedly you probably wouldn't implement this great feature as an aspect, but it's easy enough to demonstrate the idea. For illustration purposes, we don't define the pointcut expression in place but refer to a named pointcut.

*Example: Implementation of an around advice*::
	
	namespace F3\MyPackage;
	
	/**
	 * A lastname rejection aspect
	 *
	 * @aspect
	 */
	class LastNameRejectionAspect {
	
		/**
		 * A pointcut which matches all guestbook submission method invocations
		 *
		 * @pointcut method(\F3\Guestbook\SubmissionHandlingThingy->submit())
		 */
		public function guestbookSubmissionPointcut() {}
	
		/**
		 * Around advice, rejects the lastname "Sarkosh"
		 *
		 * @param  \F3\FLOW3\AOP\JoinPointInterface $joinPoint The current join point
		 * @return mixed Result of the target method
		 * @around F3\MyPackage\LastNameRejectionAspect->guestbookSubmissionPointcut
		 */
		public function rejectLastName(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {
			if ($joinPoint->getMethodArgument('lastName') == 'Sarkosh') {
				throw new Exception('Sarkosh is not a valid lastname - should be Skårhøj!');
			}
			$result = $joinPoint->getAdviceChain()->proceed($joinPoint);
			return $result;
		}
	}


Please note that if the last name is correct, we proceed with the remaining links in the advice chain. This is very important to assure that the original (target-) method is finally called. And don't forget to return the result of the advice chain ...

Introductions
=============

Introductions (also known as Inter-type Declarations) allow to subsequently implement an interface in a given target class. The (usually) newly introduced methods (required by the new interface) can then be implemented by declaring an advice. If no implementation is defined, an empty placeholder method will be generated automatically to satisfy the contract of the introduced interface.

Declaring introductions
-----------------------

Like advices, introductions are declared by annotations. But in contrast to advices, the anchor for an introduction declaration is a property of the aspect class. The annotation tag follows this syntax:

``@introduce NewInterfaceName, PointcutExpression``

Although the PoincutExpression is just a normal pointcut expression, which may also refer to named pointcuts, be aware that only expressions filtering for classes make sense. You cannot use the method() pointcut designator in this context and will typically take the class() designator instead.

The following example introduces a new interface NewInterface to the class OldClass and also provides an implementation of the method newMethod.

*Example: Declaring introductions*::
	
	namespace F3\MyPackage;
	
	/**
	 * An aspect for demonstrating introductions
	 *
	 * @aspect
	 */
	class IntroductionAspect {
	
		/**
		 * Introduces \F3\MyPackage\NewInterface to the class \F3\MyPackage\OldClass:
		 *
		 * @introduce F3\MyPackage\NewInterface, class(F3\MyPackage\OldClass)
		 */
		public $newInterface;
	
		/**
		 * Around advice, implements the new method "newMethod" of the
		 * "NewInterface" interface
		 *
		 * @param  \F3\FLOW3\AOP\JoinPointInterface $joinPoint The current join point
		 * @return void
		 * @around method(F3\MyPackage\OldClass->newMethod())
		 */
		public function newMethod(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {
				// We call the advice chain, in case any other advice is declared for
				// this method, but we don't care about the result.
			$someResult = $joinPoint->getAdviceChain->proceed($joinPoint);
	
			$a = $joinPoint->getMethodArgument('a');
			$b = $joinPoint->getMethodArgument('b');
			return $a + $b;
		}
	}

Implementation details
======================

AOP proxy mechanism
-------------------

The following diagram illustrates the building process of a proxy class:

.. figure:: ../../Images/AOPFramework_ProxyBuildingProcess.png

	Proxy building process

------

.. [#] SoC could, by the way, also mean “Self-organized criticality” or
	“Service-oriented Computing” or refer to Google's “Summer of Code” ...
.. [#] AOP was rather invented by Gregor Kiczalesand his team at the Xerox Palo
	Alto Research Center. The original implementation was called AspectJ and is an extension to Java. It still serves as a de-facto standard and is now
	maintained by the Eclipse Foundation.
.. [#] Intercepting setting and retrieval of properties can easily be achieved
	by declaring a before-, after- or around advice.
.. [#] GoF means Gang of Four and refers to the authors of the classic book
	*Design Patterns – Elements of Reusable Object-Oriented Software*
.. [#] Internally, PHP's ``preg_match()`` function is used to match the method
	name. The regular expression will be enclosed by /^...$/ (without the dots
	of course). Backslashes will be escaped to make namespace use possible
	without further hassle.