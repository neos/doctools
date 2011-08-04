============
Requirements
============

.. ============================================
.. Meta-Information for this chapter
.. ---------------------------------
.. Author: Robert Lemke
.. Converted to ReST by: Christian Müller
.. Updated for 1.0 beta1: NO
.. TODOs: none
.. ============================================

FLOW3 is being developed and tested on multiple platforms and prettyeasy to set up. Nevertheless we recommend that you go through the following list before installing FLOW3, because a server with exotic php.ini settings or wrong file permissions can easily spoil your day.

Server Environment
==================

Not surprisingly, you'll need a web server for running your FLOW3-based web
application. We recommend Apache (though IIS and others work too – we just
haven't really tested them). Please make sure that the `mod_rewrite module <http://httpd.apache.org/docs/2.3/mod/mod_rewrite.html>`_ 
is enabled.

.. warning::
	XAMPP 1.7.2a on MacOS does *not* work, it complains about syntax errors in
	the source files, probably caused by a bug in the implementation of the
	zend string optimizer.

	FLOW3's persistence mechanism requires a `PDO compatible database <http://php.net/manual/pdo.drivers.php>`_ . By
	default	we use SQLite which is bundled with the standard PHP distribution
	and doesn't require any further setup from your side. In a production
	context you'll rather want to use MySQL, PostgreSQL or the like.

PHP
===

FLOW3 was one of the first PHP projects taking advantage of namespaces and
other features introduced in PHP version 5.3. Because PHP 5.3 is not widely
installed on web servers, we created `setup guides for the
most popular platforms <http://flow3.typo3.org/documentation/reference/flow3.installingphp53/>`_ for your convenience.

The default settings and extensions of the PHP distribution should work fine
with FLOW3 but it doesn't hurt checking if the PHP modules ``mbstring`` and
``pdo_sqlite`` are enabled, especially if you compiled PHP yourself. 
You should (not only because of FLOW3) turn off magic quotes in your *php.ini*
(``magic_quotes_gpc = off``).

The development context and especially the testrunner need more than the
default amount of memory. At least during development you should raise the
memory limit to about 250 MB in your *php.ini* file.
