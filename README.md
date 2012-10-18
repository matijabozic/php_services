## About ##

This is Dependency Injection Container for PHP 5.3+. The goal of this library is to provide powerful but simple, lightweight and easy to configure Dependency Injection Container for PHP.

## About Dependency Injection Containers in general ##

Dependency Injection and Dependency Injection Container is implementation of Dependency Inversion principle in Object Oriented programming. Its "D" in SOLID principles.
Long story short, DIC is container that can instantiate and return services for you. While doing so, DIC must resolve constructor dependencies, call methods on created object etc. Objects returned by DIC are usualy called Services, but they are just objects really.

While building Service for you, DIC should tackle these problems:

- Deal with class constructor dependencies
- Deal with method dependencies
- Deal with method calls and their arguments after class has been created
- Deal with factory methods
- Distinct object dependencies from configuration variables in both constructors and methods
- Enable you to set Service visibility, not all services should be accessible through public API
- Enable you to set sharing option, so you can retrive one instance of Service if you want to

## How to configure and use Dependency Injection Container ##

To configure Dependency Injection Container you need to feed it with two arguments, `$configs` array, and `$services` array. `$configs` array should hold your configuration variables, and `$services` array should hold your Services definitions, this is where you define your object and dependencies. Variables defined in `$configs` will be used in `$services`, as you will see in examples.

To instantiate DIC you would use this:

<pre>
$dic = new \Core\Services\Container($configs, $services);
</pre>

or you can pass `$configs` and `$services` using setter methods like this:

<pre>
$dic = new \Core\Services\Container();
$dic->setConfigs($configs);
$dic->setServices($services);
</pre>

This is how `$configs` should look like:
<pre>
array(
	'database.driver'   => 'pdo_mysql',	
	'database.name'     => 'test',
	'database.username' => 'root',
	'database.password' => '*****',

	// Define any variable you want to reference in $services
 
);
</pre>

And this is how you would define simple Database service in `$services`:

<pre>
array(
	'Database' => 
		array(
			'class' => '\Core\Database\Database',
			'params' => array(:database.driver, :database.name, :database.username, :database.password),
			'shared' => false,
			'protected' => false,			
		),
);
</pre>

Now when we have $configs and $services defined and passed to $dic, $dic knows everything about Database service and how to build it.

Now you can ask for Database Service like this:
<pre>
$db = $dic->getService('Database');
</pre>

As you can see in this example Im using `:` to reference variables defined in `$configs`. All variables defined in `$configs`, can be accessed in `$services` using `:name`. This way you can place `$configs` in your `/application/config/config.php` folder and define all variables there, and then use them inside your `$services`. This way Services configuration is not hard coded into services definitions.

Now, how to define `$config` and variables used in `$services` should be clear, lets talk more about how to define services:

## Services Defintions ##

Service Definitions are defined in `$services` array. Every service defined is again array, so we have multidimensional array, where every key represents Service name that can be retrived like this:
<pre>
$dic->getService('ServiceName');
</pre>

While defining service these are all possible definitions you can use:
<pre>
$services = array(
'ServiceName' => 
	array (
		'class'     => 'ClassName',
		'factory'   => array('class'  => 'FactoryClass',
							 'method' => 'FactoryMethod', 
							 'params' => array('param1', 'param2'),
							 ),	
		'params'    => array(':name', '::service'),
		'calls'     => array('method1' => array('param1', 'param2'), 'method2' => array('param3')),
		'shared'    => false,
		'protected' => false,
	),
);
</pre>

Note that you would never ever use all of them to define a Service, if you are using factory method to build service you dont need class, and params. These are here just so you can see all options while defining your Services. Now lets go through all of them:

<pre>
ServiceName - name of your Service, you would use that name to get Service instance.
class       - methods that should be called after class is created, and arguments to pass
factory     - factory method, you should define class, method, and params that would be passed to that method.
params      - arguments that will be passed to object constructor
calls       - methods to call after class is instantiated, accepts arguments
shared      - if true, you will always get the same instance of Service, if false you get new one every time
protected   - if true, class can't be retrived using $dic->getService()
</pre>

One more thing to note, many times your Service will depend on already defined services. To reference already defined service use `::ServiceName`. And in these situations you would probably want to protect service with `protected => true`, so you cant access this Service from outside.

Thats preatty much it, now Il joust show you some examples how to define some Services like Doctrine ORM and Twig, so you can see how this works in real life.

## Examples ##

#### Twig ####

To load Twig, we need instance of `Twig_Environment`, and `Twig_Environment` needs instance of `Twig_Loader` in constructor. This is nice example that doas not use Factory methods, but have object dependencies. We would define it like this in our `$services`:

<pre>
array(
	'TwigLoader' => 
		array(
			'class' => 'Twig_Loader_Filesystem',
			'params' => array(':views.location'),
			'shared' => false,
			'protected' => true,
		),
	
	'Twig' => 
		array(
			'class' => 'Twig_Environment',
			'params' => array('::TwigLoader', array()),
			'shared' => false,
			'protected' => false,			
		),
)
</pre>

As you can see, we have two services, TwigLoader is protected and used only inside Twig Service. We reference TwigLoader with `::TwigLoader`. And we use variable `:views.location` defined in `$config`.

Now when we ask for Twig instance like this:
<pre>
$twig = $dic->getService('Twig');
</pre>

and Dependency Injection Container has all information on how to build and return Twig Service.

#### Doctrine ####

Doctrine ORM uses factory methods to return object instances, so this example shows how to use factory methods. `$services` would look like this:
<pre>
array (
	'DoctrineOrmConfig' => 
		array (
			'factory'   => array('class'  => '\Doctrine\ORM\Tools\Setup',
								 'method' => 'createAnnotationMetadataConfiguration', 
								 'params' => array(array(':entities.path'), false),
								 ),
			'calls'     => array('setProxyDir' => array(':proxies.path'),
								 'setProxyNamespace' => array(':proxies.namespace'),
								 'setAutoGenerateProxyClasses' => array(true),
								),
			'shared'    => false,
			'protected' => true,
		),
	
	'DoctrineOrm' => 
		array(
			'factory'   => array('class'  => '\Doctrine\ORM\EntityManager',
								 'method' => 'create', 
								 'params' => array(array(
								 	'driver' => ':database.driver', 
								 	'user' => ':database.username', 
								 	'password' => ':database.password', 
								 	'dbname' => ':database.name'), 
								 	'::DoctrineOrmConfig'
									),
								 ),
			'shared'    => false,
			'protected' => false,		
		),
</pre>

Again, DoctrineOrm Service depends on DoctrineOrmConfig, but this time we use factory methods.

Thats it, you should get a hold on how this all works. Its tested and works as it should.
In future, Il try to find a way to define services with less lines of code, but without using YAML and making this library depended on external libraries. I want this library to be small and portable.






