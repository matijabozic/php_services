<?php

	// Include Autoload
	require_once('src/Core/Autoload/Autoload.php');
	
	// Register Autoloading
	$autoload = new Autoload();
	$autoload->addLibrary('src/', 'Core');
	$autoload->register();
	
	// Create some dummy classes that depend on each other to test this container
	
	class User
	{
		protected $name;
		public $email;
		
		public function __construct($name)
		{
			$this->name = $name;
			//$this->email = $email;
		}
		
		public function getName()
		{
			echo $this->name;
		}	
	}
	
	// Email class
	
	class Email
	{	
		public function sendMail()
		{
			echo "Sending..";
		}
		
		public function saySomething()
		{
			echo "hi...";	
		}
	}
	
	// Import Services Container aka. Dependency Injection Container
	use Core\Services\Container;
	
	$c = new Container();
	$c->addService('email', 'Email');
	$c->setShared('email', true); // Set sharing on / true
	
	$a = $c->getService('email');
	$b = $c->getService('email');
	$c = $c->getService('email');
	
	var_dump($a);
	echo "<br />";
	var_dump($b);
	echo "<br />";
	var_dump($c);		
	echo "<br />";
		
	// Its ok every var_dump returns the same instance, so sharing IS on


	
	
	
?>