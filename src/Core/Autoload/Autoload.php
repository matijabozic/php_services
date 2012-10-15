<?php
	
	/**
	 * This file is part of MVC Core framework
	 * (c) Matija Božić, www.matijabozic.com
	 * 
	 * Autoload class implements the technical interoperability
	 * standards for PHP 5.3 class autoloading using namespaces.
	 * 
	 * More about standard: https://wiki.php.net/rfc/splclassloader
	 * 
	 * @package    Autoload
	 * @author     Matija Božić <matijabozic@gmx.com>
	 * @license    MIT - http://opensource.org/licenses/MIT
	 */
	
	class Autoload
	{
		/**
		 * List of available Libraries and their paths
		 * 
		 * @access  protected
		 * @var     array
		 */
		
		protected $libraries = array();
		
		/**
		 * List of libraries and their extensions
		 * 
		 * @access  protected
		 * @var     array
		 */
		
		protected $extensions = array();
		
		/**
		 * The namespace seperator used
		 * 
		 * @access  protected
		 * @var     string
		 */
		
		protected $namespaceSeparator = '\\';
		
		/**
		 * Adds new library to autoload
		 * 
		 * @access  public 
		 * @param   string
		 * @param   string
		 * @param   string
		 * @return  void
		 */
		
		public function addLibrary($path, $library, $extension = '.php')
		{
			$this->libraries[$library] = $path;
			$this->extensions[$library] = $extension;
		}
		
		/**
		 * Checks if library is available
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function hasLibrary($library)
		{
			return isset($this->libraries[$library]);
		}
		
		/**
		 * Removes available library
		 * 
		 * @access  public
		 * @param   string
		 * @return  void
		 */
		
		public function removeLibrary($library)
		{
			unset($this->libraries[$library]);
			unset($this->extensions[$library]);
		}
		
		/**
		 * Changes namespace separator 
		 * 
		 * @access  public
		 * @param   string
		 * @return  void
		 */
		
		public function setNamespaceSeparator($separator)
		{
			$this->namespaceSeparator = $separator;
		}
		
		/**
		 * Returns current namespace separator
		 * 
		 * @access  public
		 * @return  string 
		 */
		
		public function getNamespaceSeparator()
		{
			return $this->namespaceSeparator;
		}
		
		/**
		 * Installs this class loader on the SPL autoload stack.
		 * 
		 * @access  public
		 * @return  bool
		 */
		
		public function register($prepend = false)
		{
			return spl_autoload_register(array($this, 'loadClass'), true, $prepend);
		}
		
		/**
		 * Uninstalls this class loader from the SPL autoloader stack.
		 * 
		 * @access  public
		 * @return  bool
		 */
		
		public function unregister()
		{
			return spl_autoload_unregister(array($this, 'loadClass'));
		}
		
		/**
		 * The main class loader, loads required class or interface
		 * 
		 * @access  protected
		 * @param   string  $className
		 * @return  void
		 */
		
		public function loadClass($class)
		{	
			$library = strstr($class, $this->namespaceSeparator, true);
			
			if($this->hasLibrary($library)) {	
				$namespace = substr($class, 0, strrpos($class, $this->namespaceSeparator));
				$className = substr($class, strrpos($class, $this->namespaceSeparator) + 1);
				$path = $this->libraries[$library];
				$file = $path . str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . $this->extensions[$library];
				
				if(is_file($file)) {
					include($file);	
				}
			}
		}
	}

?>