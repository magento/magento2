<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Application_Bootstrap_Bootstrapper
 */
#require_once 'Zend/Application/Bootstrap/Bootstrapper.php';

/**
 * @see Zend_Application_Bootstrap_ResourceBootstrapper
 */
#require_once 'Zend/Application/Bootstrap/ResourceBootstrapper.php';

/**
 * Abstract base class for bootstrap classes
 *
 * @uses       Zend_Application_Bootstrap_Bootstrapper
 * @uses       Zend_Application_Bootstrap_ResourceBootstrapper
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Application_Bootstrap_BootstrapAbstract
    implements Zend_Application_Bootstrap_Bootstrapper,
               Zend_Application_Bootstrap_ResourceBootstrapper
{
    /**
     * @var Zend_Application|Zend_Application_Bootstrap_Bootstrapper
     */
    protected $_application;

    /**
     * @var array Internal resource methods (resource/method pairs)
     */
    protected $_classResources;

    /**
     * @var object Resource container
     */
    protected $_container;

    /**
     * @var string
     */
    protected $_environment;

    /**
     * Flattened (lowercase) option keys used for lookups
     *
     * @var array
     */
    protected $_optionKeys = array();

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var Zend_Loader_PluginLoader_Interface
     */
    protected $_pluginLoader;

    /**
     * @var array Class-based resource plugins
     */
    protected $_pluginResources = array();

    /**
     * @var array Initializers that have been run
     */
    protected $_run = array();

    /**
     * @var array Initializers that have been started but not yet completed (circular dependency detection)
     */
    protected $_started = array();

    /**
     * Constructor
     *
     * Sets application object, initializes options, and prepares list of
     * initializer methods.
     *
     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @throws Zend_Application_Bootstrap_Exception When invalid application is provided
     */
    public function __construct($application)
    {
        $this->setApplication($application);
        $options = $application->getOptions();
        $this->setOptions($options);
    }

    /**
     * Set class state
     *
     * @param  array $options
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     */
    public function setOptions(array $options)
    {
        $this->_options = $this->mergeOptions($this->_options, $options);

        $options = array_change_key_case($options, CASE_LOWER);
        $this->_optionKeys = array_merge($this->_optionKeys, array_keys($options));

        $methods = get_class_methods($this);
        foreach ($methods as $key => $method) {
            $methods[$key] = strtolower($method);
        }

        if (array_key_exists('pluginpaths', $options)) {
            $pluginLoader = $this->getPluginLoader();

            foreach ($options['pluginpaths'] as $prefix => $path) {
                $pluginLoader->addPrefixPath($prefix, $path);
            }
            unset($options['pluginpaths']);
        }

        foreach ($options as $key => $value) {
            $method = 'set' . strtolower($key);

            if (in_array($method, $methods)) {
                $this->$method($value);
            } elseif ('resources' == $key) {
                foreach ($value as $resource => $resourceOptions) {
                    $this->registerPluginResource($resource, $resourceOptions);
                }
            }
        }
        return $this;
    }

    /**
     * Get current options from bootstrap
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Is an option present?
     *
     * @param  string $key
     * @return bool
     */
    public function hasOption($key)
    {
        return in_array(strtolower($key), $this->_optionKeys);
    }

    /**
     * Retrieve a single option
     *
     * @param  string $key
     * @return mixed
     */
    public function getOption($key)
    {
        if ($this->hasOption($key)) {
            $options = $this->getOptions();
            $options = array_change_key_case($options, CASE_LOWER);
            return $options[strtolower($key)];
        }
        return null;
    }

    /**
     * Merge options recursively
     *
     * @param  array $array1
     * @param  mixed $array2
     * @return array
     */
    public function mergeOptions(array $array1, $array2 = null)
    {
        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($array2[$key])) {
                    $array1[$key] = (array_key_exists($key, $array1) && is_array($array1[$key]))
                                  ? $this->mergeOptions($array1[$key], $array2[$key])
                                  : $array2[$key];
                } else {
                    $array1[$key] = $val;
                }
            }
        }
        return $array1;
    }

    /**
     * Get class resources (as resource/method pairs)
     *
     * Uses get_class_methods() by default, reflection on prior to 5.2.6,
     * as a bug prevents the usage of get_class_methods() there.
     *
     * @return array
     */
    public function getClassResources()
    {
        if (null === $this->_classResources) {
            if (version_compare(PHP_VERSION, '5.2.6') === -1) {
                $class        = new ReflectionObject($this);
                $classMethods = $class->getMethods();
                $methodNames  = array();

                foreach ($classMethods as $method) {
                    $methodNames[] = $method->getName();
                }
            } else {
                $methodNames = get_class_methods($this);
            }

            $this->_classResources = array();
            foreach ($methodNames as $method) {
                if (5 < strlen($method) && '_init' === substr($method, 0, 5)) {
                    $this->_classResources[strtolower(substr($method, 5))] = $method;
                }
            }
        }

        return $this->_classResources;
    }

    /**
     * Get class resource names
     *
     * @return array
     */
    public function getClassResourceNames()
    {
        $resources = $this->getClassResources();
        return array_keys($resources);
    }

    /**
     * Register a new resource plugin
     *
     * @param  string|Zend_Application_Resource_Resource $resource
     * @param  mixed  $options
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Zend_Application_Bootstrap_Exception When invalid resource is provided
     */
    public function registerPluginResource($resource, $options = null)
    {
        if ($resource instanceof Zend_Application_Resource_Resource) {
            $resource->setBootstrap($this);
            $pluginName = $this->_resolvePluginResourceName($resource);
            $this->_pluginResources[$pluginName] = $resource;
            return $this;
        }

        if (!is_string($resource)) {
            throw new Zend_Application_Bootstrap_Exception('Invalid resource provided to ' . __METHOD__);
        }

        $this->_pluginResources[$resource] = $options;
        return $this;
    }

    /**
     * Unregister a resource from the bootstrap
     *
     * @param  string|Zend_Application_Resource_Resource $resource
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Zend_Application_Bootstrap_Exception When unknown resource type is provided
     */
    public function unregisterPluginResource($resource)
    {
        if ($resource instanceof Zend_Application_Resource_Resource) {
            if ($index = array_search($resource, $this->_pluginResources, true)) {
                unset($this->_pluginResources[$index]);
            }
            return $this;
        }

        if (!is_string($resource)) {
            throw new Zend_Application_Bootstrap_Exception('Unknown resource type provided to ' . __METHOD__);
        }

        $resource = strtolower($resource);
        if (array_key_exists($resource, $this->_pluginResources)) {
            unset($this->_pluginResources[$resource]);
        }

        return $this;
    }

    /**
     * Is the requested plugin resource registered?
     *
     * @param  string $resource
     * @return bool
     */
    public function hasPluginResource($resource)
    {
        return (null !== $this->getPluginResource($resource));
    }

    /**
     * Get a registered plugin resource
     *
     * @param string $resource
     * @return Zend_Application_Resource_Resource
     * @throws Zend_Application_Bootstrap_Exception
     */
    public function getPluginResource($resource)
    {
        if (array_key_exists(strtolower($resource), $this->_pluginResources)) {
            $resource = strtolower($resource);
            if (!$this->_pluginResources[$resource] instanceof Zend_Application_Resource_Resource) {
                $resourceName = $this->_loadPluginResource($resource, $this->_pluginResources[$resource]);
                if (!$resourceName) {
                    throw new Zend_Application_Bootstrap_Exception(sprintf('Unable to resolve plugin "%s"; no corresponding plugin with that name', $resource));
                }
                $resource = $resourceName;
            }
            return $this->_pluginResources[$resource];
        }

        foreach ($this->_pluginResources as $plugin => $spec) {
            if ($spec instanceof Zend_Application_Resource_Resource) {
                $pluginName = $this->_resolvePluginResourceName($spec);
                if (0 === strcasecmp($resource, $pluginName)) {
                    unset($this->_pluginResources[$plugin]);
                    $this->_pluginResources[$pluginName] = $spec;
                    return $spec;
                }
                continue;
            }

            if (false !== $pluginName = $this->_loadPluginResource($plugin, $spec)) {
                if (0 === strcasecmp($resource, $pluginName)) {
                    return $this->_pluginResources[$pluginName];
                }
                continue;
            }

            if (class_exists($plugin)
            && is_subclass_of($plugin, 'Zend_Application_Resource_Resource')
            ) { //@SEE ZF-7550
                $spec = (array) $spec;
                $spec['bootstrap'] = $this;
                $instance = new $plugin($spec);
                $pluginName = $this->_resolvePluginResourceName($instance);
                unset($this->_pluginResources[$plugin]);
                $this->_pluginResources[$pluginName] = $instance;

                if (0 === strcasecmp($resource, $pluginName)) {
                    return $instance;
                }
            }
        }

        return null;
    }

    /**
     * Retrieve all plugin resources
     *
     * @return array
     */
    public function getPluginResources()
    {
        foreach (array_keys($this->_pluginResources) as $resource) {
            $this->getPluginResource($resource);
        }
        return $this->_pluginResources;
    }

    /**
     * Retrieve plugin resource names
     *
     * @return array
     */
    public function getPluginResourceNames()
    {
        $this->getPluginResources();
        return array_keys($this->_pluginResources);
    }

    /**
     * Set plugin loader for loading resources
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        $this->_pluginLoader = $loader;
        return $this;
    }

    /**
     * Get the plugin loader for resources
     *
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if ($this->_pluginLoader === null) {
            $options = array(
                'Zend_Application_Resource'  => 'Zend/Application/Resource',
                'ZendX_Application_Resource' => 'ZendX/Application/Resource'
            );

            $this->_pluginLoader = new Zend_Loader_PluginLoader($options);
        }

        return $this->_pluginLoader;
    }

    /**
     * Set application/parent bootstrap
     *
     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Zend_Application_Bootstrap_Exception
     */
    public function setApplication($application)
    {
        if (($application instanceof Zend_Application)
            || ($application instanceof Zend_Application_Bootstrap_Bootstrapper)
        ) {
            if ($application === $this) {
                throw new Zend_Application_Bootstrap_Exception('Cannot set application to same object; creates recursion');
            }
            $this->_application = $application;
        } else {
            throw new Zend_Application_Bootstrap_Exception('Invalid application provided to bootstrap constructor (received "' . get_class($application) . '" instance)');
        }
        return $this;
    }

    /**
     * Retrieve parent application instance
     *
     * @return Zend_Application|Zend_Application_Bootstrap_Bootstrapper
     */
    public function getApplication()
    {
        return $this->_application;
    }

    /**
     * Retrieve application environment
     *
     * @return string
     */
    public function getEnvironment()
    {
        if (null === $this->_environment) {
            $this->_environment = $this->getApplication()->getEnvironment();
        }
        return $this->_environment;
    }

    /**
     * Set resource container
     *
     * By default, if a resource callback has a non-null return value, this
     * value will be stored in a container using the resource name as the
     * key.
     *
     * Containers must be objects, and must allow setting public properties.
     *
     * @param  object $container
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Zend_Application_Bootstrap_Exception
     */
    public function setContainer($container)
    {
        if (!is_object($container)) {
            throw new Zend_Application_Bootstrap_Exception('Resource containers must be objects');
        }
        $this->_container = $container;
        return $this;
    }

    /**
     * Retrieve resource container
     *
     * @return object
     */
    public function getContainer()
    {
        if (null === $this->_container) {
            $this->setContainer(new Zend_Registry());
        }
        return $this->_container;
    }

    /**
     * Determine if a resource has been stored in the container
     *
     * During bootstrap resource initialization, you may return a value. If
     * you do, it will be stored in the {@link setContainer() container}.
     * You can use this method to determine if a value was stored.
     *
     * @param  string $name
     * @return bool
     */
    public function hasResource($name)
    {
        $resource  = strtolower($name);
        $container = $this->getContainer();
        return isset($container->{$resource});
    }

    /**
     * Retrieve a resource from the container
     *
     * During bootstrap resource initialization, you may return a value. If
     * you do, it will be stored in the {@link setContainer() container}.
     * You can use this method to retrieve that value.
     *
     * If no value was returned, this will return a null value.
     *
     * @param  string $name
     * @return null|mixed
     */
    public function getResource($name)
    {
        $resource  = strtolower($name);
        $container = $this->getContainer();
        if ($this->hasResource($resource)) {
            return $container->{$resource};
        }
        return null;
    }

    /**
     * Implement PHP's magic to retrieve a resource
     * in the bootstrap
     *
     * @param string $prop
     * @return null|mixed
     */
    public function __get($prop)
    {
        return $this->getResource($prop);
    }

    /**
     * Implement PHP's magic to ask for the
     * existence of a resource in the bootstrap
     *
     * @param string $prop
     * @return bool
     */
    public function __isset($prop)
    {
        return $this->hasResource($prop);
    }

    /**
     * Bootstrap individual, all, or multiple resources
     *
     * Marked as final to prevent issues when subclassing and naming the
     * child class 'Bootstrap' (in which case, overriding this method
     * would result in it being treated as a constructor).
     *
     * If you need to override this functionality, override the
     * {@link _bootstrap()} method.
     *
     * @param  null|string|array $resource
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Zend_Application_Bootstrap_Exception When invalid argument was passed
     */
    final public function bootstrap($resource = null)
    {
        $this->_bootstrap($resource);
        return $this;
    }

    /**
     * Overloading: intercept calls to bootstrap<resourcename>() methods
     *
     * @param  string $method
     * @param  array  $args
     * @return Zend_Application_Bootstrap_BootstrapAbstract
     * @throws Zend_Application_Bootstrap_Exception On invalid method name
     */
    public function __call($method, $args)
    {
        if (9 < strlen($method) && 'bootstrap' === substr($method, 0, 9)) {
            $resource = substr($method, 9);
            return $this->bootstrap($resource);
        }

        throw new Zend_Application_Bootstrap_Exception('Invalid method "' . $method . '"');
    }

    /**
     * Bootstrap implementation
     *
     * This method may be overridden to provide custom bootstrapping logic.
     * It is the sole method called by {@link bootstrap()}.
     *
     * @param  null|string|array $resource
     * @return void
     * @throws Zend_Application_Bootstrap_Exception When invalid argument was passed
     */
    protected function _bootstrap($resource = null)
    {
        if (null === $resource) {
            foreach ($this->getClassResourceNames() as $resource) {
                $this->_executeResource($resource);
            }

            foreach ($this->getPluginResourceNames() as $resource) {
                $this->_executeResource($resource);
            }
        } elseif (is_string($resource)) {
            $this->_executeResource($resource);
        } elseif (is_array($resource)) {
            foreach ($resource as $r) {
                $this->_executeResource($r);
            }
        } else {
            throw new Zend_Application_Bootstrap_Exception('Invalid argument passed to ' . __METHOD__);
        }
    }

    /**
     * Execute a resource
     *
     * Checks to see if the resource has already been run. If not, it searches
     * first to see if a local method matches the resource, and executes that.
     * If not, it checks to see if a plugin resource matches, and executes that
     * if found.
     *
     * Finally, if not found, it throws an exception.
     *
     * @param  string $resource
     * @return void
     * @throws Zend_Application_Bootstrap_Exception When resource not found
     */
    protected function _executeResource($resource)
    {
        $resourceName = strtolower($resource);

        if (in_array($resourceName, $this->_run)) {
            return;
        }

        if (isset($this->_started[$resourceName]) && $this->_started[$resourceName]) {
            throw new Zend_Application_Bootstrap_Exception('Circular resource dependency detected');
        }

        $classResources = $this->getClassResources();
        if (array_key_exists($resourceName, $classResources)) {
            $this->_started[$resourceName] = true;
            $method = $classResources[$resourceName];
            $return = $this->$method();
            unset($this->_started[$resourceName]);
            $this->_markRun($resourceName);

            if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }

            return;
        }

        if ($this->hasPluginResource($resource)) {
            $this->_started[$resourceName] = true;
            $plugin = $this->getPluginResource($resource);
            $return = $plugin->init();
            unset($this->_started[$resourceName]);
            $this->_markRun($resourceName);

            if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }

            return;
        }

        throw new Zend_Application_Bootstrap_Exception('Resource matching "' . $resource . '" not found');
    }

    /**
     * Load a plugin resource
     *
     * @param  string $resource
     * @param  array|object|null $options
     * @return string|false
     */
    protected function _loadPluginResource($resource, $options)
    {
        $options   = (array) $options;
        $options['bootstrap'] = $this;
        $className = $this->getPluginLoader()->load(strtolower($resource), false);

        if (!$className) {
            return false;
        }

        $instance = new $className($options);

        unset($this->_pluginResources[$resource]);

        if (isset($instance->_explicitType)) {
            $resource = $instance->_explicitType;
        }
        $resource = strtolower($resource);
        $this->_pluginResources[$resource] = $instance;

        return $resource;
    }

    /**
     * Mark a resource as having run
     *
     * @param  string $resource
     * @return void
     */
    protected function _markRun($resource)
    {
        if (!in_array($resource, $this->_run)) {
            $this->_run[] = $resource;
        }
    }

    /**
     * Resolve a plugin resource name
     *
     * Uses, in order of preference
     * - $_explicitType property of resource
     * - Short name of resource (if a matching prefix path is found)
     * - class name (if none of the above are true)
     *
     * The name is then cast to lowercase.
     *
     * @param  Zend_Application_Resource_Resource $resource
     * @return string
     */
    protected function _resolvePluginResourceName($resource)
    {
        if (isset($resource->_explicitType)) {
            $pluginName = $resource->_explicitType;
        } else  {
            $className  = get_class($resource);
            $pluginName = $className;
            $loader     = $this->getPluginLoader();
            foreach ($loader->getPaths() as $prefix => $paths) {
                if (0 === strpos($className, $prefix)) {
                    $pluginName = substr($className, strlen($prefix));
                    $pluginName = trim($pluginName, '_');
                    break;
                }
            }
        }
        $pluginName = strtolower($pluginName);
        return $pluginName;
    }
}
