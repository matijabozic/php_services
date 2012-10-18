<?php

	/**
	 * This file is part of MVC Core framework
	 * (c) Matija Božić, www.matijabozic.com
	 * 
	 * Dependency Injection Container for PHP 5.3+
	 * 
	 * @package    Services
	 * @author     Matija Božić <matijabozic@gmx.com>
	 * @license    MIT - http://opensource.org/licenses/MIT
	 */
	
	namespace Core\Services;
	
	class Container
	{	
		/**
		 * Configs array, holds configuration values
		 * 
		 * @access  public
		 * @var     array
		 */
		
		protected $configs = array();
		
		/**
		 * Services descriptions needed to build service properly
		 * 
		 * @access  public
		 * @var     array
		 */
		 
		protected $services = array();

		/**
		 * Shared, already instanciated classes
		 * 
		 * @access  public
		 * @var     array
		 */
		
		protected $shared = array();
		
		/**
		 * Services constructor
		 * 
		 * @access  public
		 * @param   array
		 * @param   array
		 * @return  void
		 */
		
		public function __construct(array $configs = null, array $services = null)
		{
			if($configs === null XOR $services === null) {
				throw new \InvalidArgumentException('Supply both configs and services or none!');	
			}
			
			if($configs AND $services) {
				$this->configs = $configs;
				$this->services = $services;	
			}
		}		
		
		/**
		 * Adds config array from file as array
		 * 
		 * @access  public
		 * @param   array
		 * @return  void
		 */
		
		public function setConfigs(array $configs)
		{
			$this->configs = $configs;
		}
		
		/**
		 * Adds services definitions from file as array
		 * 
		 * @access  public
		 * @param   array
		 * @return  void
		 */
		
		public function setServices(array $services)
		{
			$this->services = $services;
		}
		
		/**
		 * Set config key and value
		 * 
		 * @access  public
		 * @param   string
		 * @param   string
		 * @return  void
		 */
		
		public function addConfig($key, $value)
		{
			$this->configs[$key] = $value;
		}
		
		/**
		 * Check if given config key is set
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function hasConfig($key)
		{
			if(isset($this->configs[$key])) {
				return true;
			}
			return false;
		}
		
		/**
		 * Get config value for given key
		 * 
		 * @access  public
		 * @param   string
		 * @return  string
		 */
		
		public function getConfig($key)
		{
			return $this->configs[$key];
		}	
				
		/**
		 * Sets new service
		 * 
		 * @access  public
		 * @param   string
		 * @param   string
		 * @return  void
		 */
		
		public function addService($id, $class)
		{
			$this->services[$id]['class'] = $class;
			$this->services[$id]['params'] = null;
			$this->services[$id]['calls'] = null;
			$this->services[$id]['factory'] = null;
			$this->services[$id]['shared'] = false;
			$this->services[$id]['protected'] = false;
		}
		
		/**
		 * Checks if there is service defined
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function hasService($id)
		{
			if(isset($this->services[$id]))
			{
				return true;
			}
			return false;
		}
		
		/**
		 * Returns requested service instance
		 * 
		 * @access  public
		 * @param   string
		 * @return  object
		 */
		
		public function getService($id)
		{
			if($this->isProtected($id)) {
				return false;	
			}
			
			if($this->isShared($id)) {
				if(!isset($this->shared[$id])) {
					$this->shared[$id] = $this->buildService($id);
				}
				return $this->shared[$id];		
			} else {
				return $this->buildService($id);
			}
		}
	
		/**
		 * Set constructor parameters to service
		 * 
		 * @access  public
		 * @param   string
		 * @param   array
		 */
		
		public function addParams($id, array $params)
		{
			$this->services[$id]['params'] = $params;
		}
		
		/**
		 * Check if service has constructor params
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function hasParams($id)
		{
			if(isset($this->services[$id]['params'])) {
				return true;
			}
			return false;
		}
		
		/**
		 * Retrive constructor parameters 
		 * 
		 * @access  public 
		 * @param   string
		 * @return  array
		 */
		
		public function getParams($id)
		{
			return $this->services[$id]['params'];
		}
		
		/**
		 * Add setter method call to service
		 * 
		 * @access  public 
		 * @param   string
		 * @param   string 
		 * @param   array
		 * @return  void
		 */
		
		public function addCall($id, $call, array $params = array())
		{
			$this->services[$id]['calls'][$call] = $params;
		}
		
		/**
		 * Check if given service has calls
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function hasCalls($id)
		{
			if(isset($this->services[$id]['calls'])) {
				return true;
			}
			return false;
		}
		
		/**
		 * Return all service calls with param data
		 * 
		 * @access  public 
		 * @param   string
		 * @return  array
		 */
		
		public function getCalls($id) 
		{
			return $this->services[$id]['calls'];
		}
		
		/**
		 * add factory method for given service
		 * 
		 * @access  public
		 * @param   string
		 * @param   string
		 * @param   string
		 * @param   array
		 * @return  void
		 */
		
		public function addFactory($id, $class, $method, array $params = null)	
		{
			$this->services[$id]['factory'] = array('class' => $class, 'method' => $method, 'params' => $params);
		}
		
		/**
		 * Check if given service has factory class
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function hasFactory($id)
		{
			if(isset($this->services[$id]['factory'])) {
				return true;
			}
			return false;
		}
		
		/**
		 * Return factory params for given service
		 * 
		 * @access  public
		 * @param   string
		 * @return  array
		 */
		
		public function getFactory($id) 
		{
			return $this->services[$id]['factory'];
		}
		
		/**
		 * Set class shared, or not shared
		 * 
		 * @access  public
		 * @param   string
		 * @param   bool
		 * @return  void
		 */
		
		public function setShared($id, $value)
		{
			$this->services[$id]['shared'] = $value;
		}
		
		/**
		 * Check if class sharing is set
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function hasShared($id)
		{
			if(isset($this->services[$id]['shared'])) {
				return true;
			}
			return false;
		}
		
		/**
		 * Get class sharing value
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function getShared($id)
		{
			return $this->services[$id]['shared'];
		}
		
		/**
		 * Check if class is shared or not
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function isShared($id)
		{
			if($this->services[$id]['shared'] == true) {
				return true;	
			}
			return false;
		}
		
		/**
		 * Set service protection / visibility
		 * 
		 * @access  public
		 * @param   string
		 * @param   bool
		 * @return  void
		 */
		
		public function setProtection($id, $value)
		{
			$this->services[$id]['protected'] = $value;
		}
		
		/**
		 * Return protection value
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function getProtection($id)
		{
			return $this->services[$id]['protected'];
		}
		
		/**
		 * Check if protection is set
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function hasProtection($id)
		{
			if(isset($this->services[$id]['protected'])) {
				return true;	
			}
			return false;
		}
		
		/**
		 * Check if service is protected / visible or not
		 * 
		 * @access  public
		 * @param   string
		 * @return  bool
		 */
		
		public function isProtected($id) 
		{
			if($this->services[$id]['protected'] == true) {
				return true;	
			}
			return false;
		}
		
		/**
		 * Return class name for given service
		 * 
		 * @access  protected
		 * @param   string
		 * @return  string
		 */
		
		protected function getClassName($id) 
		{
			return $this->services[$id]['class'];
		}			
		
		/**
		 * Return factory class for given service
		 * 
		 * @access  protected
		 * @param   string
		 * @return  string
		 */
		
		protected function getFactoryClass($id) 
		{
			return $this->services[$id]['factory']['class'];
		}
		
		/**
		 * Return factory method for given class
		 * 
		 * @access  protected
		 * @param   string
		 * @return  string
		 */
		
		protected function getFactoryMethod($id) 
		{
			return $this->services[$id]['factory']['method'];
		}
		
		/**
		 * Return factory method params
		 * 
		 * @access  protected
		 * @param   string
		 * @return  array
		 */
		
		protected function getFactoryParams($id) 
		{
			return $this->services[$id]['factory']['params'];
		}		
		
		/**
		 * Check if value is configuration token
		 * 
		 * @access  protected
		 * @param   string
		 * @return  bool
		 */
		
		protected function isConfigToken($value) 
		{
			if(preg_match('/^:[^:]*$/', $value)) {
				return true;
			}
			return false;
		}
		
		/**
		 * Check if value is service token
		 * 
		 * @access  protected
		 * @param   string
		 * @return  bool
		 */
		
		protected function isServiceToken($value)
		{
			if(preg_match('/^::[^:]*$/', $value)) {
				return true;
			}
			return false;
		}
		
		/**
		 * Resolve parameter values, replace tokens with appropriate values
		 * 
		 * @access  protected
		 * @param   array
		 * @return  array
		 */
		
		protected function resolveValues($params)
		{
			$resolved = $params;
			
			foreach($resolved as $key => $value) {
				
				if(is_string($value)) {
					if($this->isConfigToken($value)) {
						$tkn = str_replace(':', '', $value);
						$resolved[$key] = $this->getConfig($tkn);
					}
					
					if($this->isServiceToken($value)) {
						$tkn = str_replace('::', '', $value);
						$resolved[$key] = $this->buildService($tkn);
					}	
				}
				
				else if(is_array($value)) {
					$resolved[$key] = $this->resolveValues($value);
				}
			}
			
			return $resolved;
		}

		/**
		 * Builds and returns requested service
		 * 
		 * @access  protected
		 * @param   string
		 * @return  object  
		 */

		protected function buildService($id)
		{
			if($this->hasFactory($id)) {
				$params = $this->resolveValues($this->getFactoryParams($id));				
				$service = call_user_func_array(array($this->getFactoryClass($id), $this->getFactoryMethod($id)), $params);
			} else if($this->hasParams($id)) {
				$reflection = new \ReflectionClass($this->getClassName($id));
				$params = $this->resolveValues($this->getParams($id));
				$service = $reflection->newInstanceArgs($params);
			} else {
				$class = $this->getClassName($id);
				$service = new $class();
			}
			
			if($this->hasCalls($id)) {
				foreach($this->getCalls($id) as $call => $params) {
					$resolved = $this->resolveValues($params);
					call_user_func_array(array($service, $call), $resolved);
				}
			}
			
			return $service;
		}
	}

?>