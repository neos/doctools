=============
Configuration
=============

.. ============================================
.. Meta-Information for this chapter
.. ---------------------------------
.. Author: Robert Lemke
.. Converted to ReST by: Christian Müller
.. Updated for 1.0 beta1: YES
.. TODOs: none
.. ============================================

Contexts
========

Once you start developing an application you'll want to launch it in different
contexts: in a production context the configuration must be optimized for speed
and security while in a development context debugging capabilities and
convenience are more important. FLOW3 supports the notion of contexts which
allow for bundling configuration for different purposes. Each FLOW3 request
acts in exactly one context. However, it is possible to use the same
installation on the same server in distinct contexts by accessing it through a
different host name, port or passing special arguments.

.. sidebar:: **Why do I want contexts?**

	Imagine your application is running on a live server and your customer
	reports a bug. No matter how hard you try, you can't reproduce the issue on
	your local development server. Now contexts allow you to enter the live
	application on the production server in a development context without
	anyone noticing – both contexts run in parallel. This effectively allows
	you to debug an application in its realistic environment (although you
	still should do the actual development on a dedicated machine ...).

	An additional use for context is the simplified staging of your application.
	You'll want almost the same configuration on your production and your
	development server - but not exactly the same. The live environment will
	surely access a different database or might require other authentication
	methods. What you do in this case is sharing most of the configuration and
	define the difference in dedicated contexts.

By default FLOW3 provides configuration for the Production and Development
context. In the standard distribution a reasonable configuration is defined for
each context:

*	In the **Production context** all caches are enabled, logging is reduced to
	a minimum and only generic, friendly error messages are displayed to the
	user (more detailed descriptions end up in the log).

*	In **Development context** caches are active but a smart monitoring service
	flushes caches automatically if PHP code or configuration has been altered.
	Error messages and exceptions are displayed verbosely and additional aids
	are given for effective development.

.. tip::
	If FLOW3 throws some strange errors at you after you made code changes,
	make sure to either manually flush the cache or run the application in
	``Development`` context - because caches are not flushed automatically
	in ``Production`` context.

The configuration for each context is located in directories of the same name:

**Context Configurations**

============================	==================================================
Directory						Description
============================	==================================================
*Configuration/*				Global configuration, for all contexts
*Configuration/Development/*	Configuration for the ``Development`` context
*Configuration/Production/*		Configuration for the ``Production`` context
============================	==================================================

Configuring FLOW3
=================

FLOW3 should work fine with the default configuration delivered with the
distribution. However, there are many switches you can adjust: use a different
database engine, specify another location for logging, select a faster cache
backend and much more. The easiest way to find ot which options are available
is taking a look at the default configuration of the FLOW3 package and other
packages. The respective files are located in
*Packages/Framework/<packageKey>/Configuration/*. Don't modify these files
directly but rather copy the setting you'd like to change and insert it into a
file within the global or context configuration directories.

FLOW3 uses the YAML format [#]_ for its configuration files. If you never edited
a YAML file, you need to know that indenting has a special meaning and tabs are
not allowed.

More detailed information about FLOW3's configuration management can be found
in the `Reference Manual<http://flow3.typo3.org/documentation/>`\ .

.. note::
	If you're running FLOW3 on a Windows machine, you do have to make some
	adjustments to the standard configuration because it will cause problems
	with long paths and filenames. By default FLOW3 caches files within the
	*Data/Temporary/<Context>/Caches/* directory
	whose absolute path can eventually become too long for Windows (isn't that
	spooky?).

	To avoid errors you should change the cache configuration so it points to a
	location with a very short absolute file path, for example *C:\\tmp\\* .
	Do that by adding the following line to the file
	*Configuration/Settings.yaml* :

	``utility: environment: temporaryDirectoryBase: 'C\\:tmp\\'``

.. important::
	Parsing the YAML configuration files takes a bit of time which remarkably
	slows down the initialization of FLOW3. That's why all configuration is
	cached by default when FLOW3 is running in Production context. Because this
	cache cannot be cleared automatically it is important to know that changes
	to any configuration file won't have any effect until you manually flush
	the respective caches.

	To avoid any hassle we recommend that you stay in Development context
	throughout this tutorial.

-----

.. [#] **YAML Ain't Markup Language** http://yaml.org