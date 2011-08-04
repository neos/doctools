=================
Signals and Slots
=================

Signal-Slot Event Handling
==========================

The concept of *Signals* and *Slots* has been introduced by the Qt toolkit and allows
for easy implementation of the Observer pattern in software.

A Signal, which contains event information as it makes sense in the case at hand, can be
emitted (sent) by any part of the code and is received by one or more Slots, which can be
any function in FLOW3. Almost no registration, deregistration or invocation code need be
written, because FLOW3 automatically generates the needed infrastructure using AOP.

Defining and using signals
--------------------------

To define a signal, simply create a method stub and annotate it with a ``@signal``
annotation:

*Example: Definition of Signal in PHP* ::

	/**
	 * @param Comment $comment
	 * @return void
	 * @signal
	 */
	protected function emitCommentCreated(Comment $comment) {} 
The method signature can be freely defined to fit the needs of     the event that is to be
signalled. Whatever parameters are defined will be handed over as given to any slots
listening to that signal.

.. note::

	The ``@signal`` annotation is picked up by the AOP framework and the method is advised
	as needed to actually do something when the signal is emitted.

To emit a signal in your code, simply call the signal method whenever it makes sense,
like in this example:

*Example: Emitting a Signal* ::

	/** 	 * @param Comment $newComment 	 * @return void 	 */
	public function createAction(Comment $newComment) {
		... 		$this->emitCommentCreated($newComment); 		... 	}

The signal will be dispatched to all slots listening to it.

Defining slots
--------------

Basically any method of any class can be used as a slot, even if never written
specifically for being a slot. The only requirement is a matching signature between signal
and slot, so that the parameters passed to the signal can be handed over to the slot
without problems. The following shows a slot, as you can see it differs in no way from any
non-slot method.

*Example: A method that can be used as a slot* ::

	/** 	 * @param Comment $comment
	 * @return void
	 */
	public function sendNewCommentNotification(Comment $comment) {  		$mail = new \TYPO3\SwiftMailer\Message();
		$mail->setFrom(array('john@doe.org ' => 'John Doe'))
			->setTo(array('karsten@typo3.org ' => 'Karsten Dambekalns'))
			->setSubject('New comment')
			->setBody($comment->getContent())
			->send();
	} 
Depending on the wiring there might be an extra parameter being given to the slot that
contains the classname and method name of the signal emitter, seperated by ``::``.

Wiring signals and slots together
---------------------------------

Which slot is actually listening for which signal is configured ("wired") in the bootstrap
code of a package. Any package can of course freely wire it's own signals to it's own
slots, but also wiring any other signal to any other slot is possible. You should be a
little careful when wiring your own or even other package's signals to slots in other
packages, as the results could be non-obvious to someone using your package.

When FLOW3 initializes, it runs the ``boot`` method in a package's ``Package`` class. This
is the place to wire signals to slots as needed for your package:

*Example: Wiring signals and slots together* ::

	/**
	 * Boot the package. We wire some signals to slots here.
	 *
	 * @param \TYPO3\FLOW3\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\FLOW3\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher(); 		$dispatcher->connect(
			'Some\Package\Controller\CommentController', 'commentCreated',
			'Some\Package\Service\Notification', 'sendNewCommentNotification'
		);
	} 
The first pair of parameters given to to ``connect`` identifies the signal you want to
wire, the second pair the slot. Both pairs consist of classname and methodname.

An alternative way of specifying the slot is to give an object instead of a classname to
the ``connect`` method. This can also be used to use a ``Closure`` instance to react to
signals, in this case the slot method name can be omitted.

There is one more parameter available here, ``$passSignalInformation``. It controls
whether or not the passing of signal information (classname and methodname of the signal
emitter, seperated by ``::``) to the slot is omitted and defaults to ``TRUE`` (for example
the information is passed).