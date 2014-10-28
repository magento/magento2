<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_ServiceManager
 */

namespace Zend\ServiceManager;

use Closure;
use ReflectionClass;

/**
 * @category Zend
 * @package  Zend_ServiceManager
 */
class ServiceManager implements ServiceLocatorInterface
{

    /**@#+
     * Constants
     */
    const SCOPE_PARENT = 'parent';
    const SCOPE_CHILD = 'child';
    /**@#-*/

    /**
     * Lookup for canonicalized names.
     *
     * @var array
     */
    protected $canonicalNames = array();

    /**
     * @var bool
     */
    protected $allowOverride = false;

    /**
     * @var array
     */
    protected $invokableClasses = array();

    /**
     * @var string|callable|Closure|FactoryInterface[]
     */
    protected $factories = array();

    /**
     * @var AbstractFactoryInterface[]
     */
    protected $abstractFactories = array();

    /**
     * @var array
     */
    protected $pendingAbstractFactoryRequests = array();

    /**
     * @var array
     */
    protected $shared = array();

    /**
     * Registered services and cached values
     *
     * @var array
     */
    protected $instances = array();

    /**
     * @var array
     */
    protected $aliases = array();

    /**
     * @var array
     */
    protected $initializers = array();

    /**
     * @var ServiceManager[]
     */
    protected $peeringServiceManagers = array();

    /**
     * Whether or not to share by default
     *
     * @var bool
     */
    protected $shareByDefault = true;

    /**
     * @var bool
     */
    protected $retrieveFromPeeringManagerFirst = false;

    /**
     * @var bool Track whether not to throw exceptions during create()
     */
    protected $throwExceptionInCreate = true;

    /**
     * @var array map of characters to be replaced through strtr
     */
    protected $canonicalNamesReplacements = array('-' => '', '_' => '', ' ' => '', '\\' => '', '/' => '');

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config = null)
    {
        if ($config) {
            $config->configureServiceManager($this);
        }
    }

    /**
     * Set allow override
     *
     * @param $allowOverride
     * @return ServiceManager
     */
    public function setAllowOverride($allowOverride)
    {
        $this->allowOverride = (bool) $allowOverride;
        return $this;
    }

    /**
     * Get allow override
     *
     * @return bool
     */
    public function getAllowOverride()
    {
        return $this->allowOverride;
    }

    /**
     * Set flag indicating whether services are shared by default
     *
     * @param  bool $shareByDefault
     * @return ServiceManager
     * @throws Exception\RuntimeException if allowOverride is false
     */
    public function setShareByDefault($shareByDefault)
    {
        if ($this->allowOverride === false) {
            throw new Exception\RuntimeException(sprintf(
                '%s: cannot alter default shared service setting; container is marked immutable (allow_override is false)',
                __METHOD__
            ));
        }
        $this->shareByDefault = (bool) $shareByDefault;
        return $this;
    }

    /**
     * Are services shared by default?
     *
     * @return bool
     */
    public function shareByDefault()
    {
        return $this->shareByDefault;
    }

    /**
     * Set throw exceptions in create
     *
     * @param  bool $throwExceptionInCreate
     * @return ServiceManager
     */
    public function setThrowExceptionInCreate($throwExceptionInCreate)
    {
        $this->throwExceptionInCreate = $throwExceptionInCreate;
        return $this;
    }

    /**
     * Get throw exceptions in create
     *
     * @return bool
     */
    public function getThrowExceptionInCreate()
    {
        return $this->throwExceptionInCreate;
    }

    /**
     * Set flag indicating whether to pull from peering manager before attempting creation
     *
     * @param  bool $retrieveFromPeeringManagerFirst
     * @return ServiceManager
     */
    public function setRetrieveFromPeeringManagerFirst($retrieveFromPeeringManagerFirst = true)
    {
        $this->retrieveFromPeeringManagerFirst = (bool) $retrieveFromPeeringManagerFirst;
        return $this;
    }

    /**
     * Should we retrieve from the peering manager prior to attempting to create a service?
     *
     * @return bool
     */
    public function retrieveFromPeeringManagerFirst()
    {
        return $this->retrieveFromPeeringManagerFirst;
    }

    /**
     * Set invokable class
     *
     * @param  string  $name
     * @param  string  $invokableClass
     * @param  bool $shared
     * @return ServiceManager
     * @throws Exception\InvalidServiceNameException
     */
    public function setInvokableClass($name, $invokableClass, $shared = true)
    {
        $cName = $this->canonicalizeName($name);
        $rName = $name;

        if ($this->has(array($cName, $rName), false)) {
            if ($this->allowOverride === false) {
                throw new Exception\InvalidServiceNameException(sprintf(
                    'A service by the name or alias "%s" already exists and cannot be overridden; please use an alternate name',
                    $cName
                ));
            }
            $this->unregisterService($cName);
        }

        $this->invokableClasses[$cName] = $invokableClass;
        $this->shared[$cName]           = (bool) $shared;

        return $this;
    }

    /**
     * Set factory
     *
     * @param  string                           $name
     * @param  string|FactoryInterface|callable $factory
     * @param  bool                             $shared
     * @return ServiceManager
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidServiceNameException
     */
    public function setFactory($name, $factory, $shared = true)
    {
        $cName = $this->canonicalizeName($name);
        $rName = $name;

        if (!is_string($factory) && !$factory instanceof FactoryInterface && !is_callable($factory)) {
            throw new Exception\InvalidArgumentException(
                'Provided abstract factory must be the class name of an abstract factory or an instance of an AbstractFactoryInterface.'
            );
        }

        if ($this->has(array($cName, $rName), false)) {
            if ($this->allowOverride === false) {
                throw new Exception\InvalidServiceNameException(sprintf(
                    'A service by the name or alias "%s" already exists and cannot be overridden, please use an alternate name',
                    $cName
                ));
            }
            $this->unregisterService($cName);
        }

        $this->factories[$cName] = $factory;
        $this->shared[$cName]    = (bool) $shared;

        return $this;
    }

    /**
     * Add abstract factory
     *
     * @param  AbstractFactoryInterface|string $factory
     * @param  bool                            $topOfStack
     * @return ServiceManager
     * @throws Exception\InvalidArgumentException if the abstract factory is invalid
     */
    public function addAbstractFactory($factory, $topOfStack = true)
    {
        if (!is_string($factory) && !$factory instanceof AbstractFactoryInterface) {
            throw new Exception\InvalidArgumentException(
                'Provided abstract factory must be the class name of an abstract factory or an instance of an AbstractFactoryInterface.'
            );
        }
        if (is_string($factory)) {
            if (!class_exists($factory, true)) {
                throw new Exception\InvalidArgumentException(
                    'Provided abstract factory must be the class name of an abstract factory or an instance of an AbstractFactoryInterface.'
                );
            }
            $refl = new ReflectionClass($factory);
            if (!$refl->implementsInterface(__NAMESPACE__ . '\\AbstractFactoryInterface')) {
                throw new Exception\InvalidArgumentException(
                    'Provided abstract factory must be the class name of an abstract factory or an instance of an AbstractFactoryInterface.'
                );
            }
        }

        if ($topOfStack) {
            array_unshift($this->abstractFactories, $factory);
        } else {
            array_push($this->abstractFactories, $factory);
        }
        return $this;
    }

    /**
     * Add initializer
     *
     * @param  callable|InitializerInterface $initializer
     * @param  bool                          $topOfStack
     * @return ServiceManager
     * @throws Exception\InvalidArgumentException
     */
    public function addInitializer($initializer, $topOfStack = true)
    {
        if (!is_callable($initializer) && !$initializer instanceof InitializerInterface) {
            if (!is_string($initializer)
                || !$this->isSubclassOf($initializer, __NAMESPACE__ . '\InitializerInterface')
            ) {
                throw new Exception\InvalidArgumentException('$initializer should be callable.');
            }
            $initializer = new $initializer;
        }

        if ($topOfStack) {
            array_unshift($this->initializers, $initializer);
        } else {
            array_push($this->initializers, $initializer);
        }
        return $this;
    }

    /**
     * Register a service with the locator
     *
     * @param  string  $name
     * @param  mixed   $service
     * @param  bool    $shared
     * @return ServiceManager
     * @throws Exception\InvalidServiceNameException
     */
    public function setService($name, $service, $shared = true)
    {
        $cName = $this->canonicalizeName($name);

        if ($this->has($cName, false)) {
            if ($this->allowOverride === false) {
                throw new Exception\InvalidServiceNameException(sprintf(
                    '%s: A service by the name "%s" or alias already exists and cannot be overridden, please use an alternate name.',
                    __METHOD__,
                    $name
                ));
            }
            $this->unregisterService($cName);
        }

        $this->instances[$cName] = $service;
        $this->shared[$cName]    = (bool) $shared;
        return $this;
    }

    /**
     * @param  string $name
     * @param  bool   $isShared
     * @return ServiceManager
     * @throws Exception\ServiceNotFoundException
     */
    public function setShared($name, $isShared)
    {
        $cName = $this->canonicalizeName($name);

        if (
            !isset($this->invokableClasses[$cName])
            && !isset($this->factories[$cName])
            && !$this->canCreateFromAbstractFactory($cName, $name)
        ) {
            throw new Exception\ServiceNotFoundException(sprintf(
                '%s: A service by the name "%s" was not found and could not be marked as shared',
                __METHOD__,
                $name
            ));
        }

        $this->shared[$cName] = (bool) $isShared;
        return $this;
    }

    /**
     * Retrieve a registered instance
     *
     * @param  string  $name
     * @param  bool    $usePeeringServiceManagers
     * @throws Exception\ServiceNotFoundException
     * @return object|array
     */
    public function get($name, $usePeeringServiceManagers = true)
    {
        $cName = $this->canonicalizeName($name);
        $rName = $name;

        if ($this->hasAlias($cName)) {
            do {
                $cName = $this->aliases[$cName];
            } while ($this->hasAlias($cName));

            if (!$this->has(array($cName, $rName))) {
                throw new Exception\ServiceNotFoundException(sprintf(
                    'An alias "%s" was requested but no service could be found.',
                    $name
                ));
            }
        }

        if (isset($this->instances[$cName])) {
            return $this->instances[$cName];
        }

        $instance                        = null;
        $retrieveFromPeeringManagerFirst = $this->retrieveFromPeeringManagerFirst();

        if ($usePeeringServiceManagers && $retrieveFromPeeringManagerFirst) {
            $instance = $this->retrieveFromPeeringManager($name);
        }
        if (!$instance) {
            if ($this->canCreate(array($cName, $rName))) {
                $instance = $this->create(array($cName, $rName));
            } elseif ($usePeeringServiceManagers && !$retrieveFromPeeringManagerFirst) {
                $instance = $this->retrieveFromPeeringManager($name);
            }
        }

        // Still no instance? raise an exception
        if (!$instance && !is_array($instance)) {
            throw new Exception\ServiceNotFoundException(sprintf(
                '%s was unable to fetch or create an instance for %s',
                    __METHOD__,
                    $name
                )
            );
        }

        if ($this->shareByDefault() && (!isset($this->shared[$cName]) || $this->shared[$cName] === true)
        ) {
            $this->instances[$cName] = $instance;
        }

        return $instance;
    }

    /**
     * Create an instance
     *
     * @param  string|array $name
     * @return false|object
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\ServiceNotCreatedException
     */
    public function create($name)
    {
        $instance = false;

        if (is_array($name)) {
            list($cName, $rName) = $name;
        } else {
            $rName = $name;
            $cName = $this->canonicalizeName($rName);
        }


        if (isset($this->factories[$cName])) {
            $instance = $this->createFromFactory($cName, $rName);
        }

        if (!$instance && isset($this->invokableClasses[$cName])) {
            $instance = $this->createFromInvokable($cName, $rName);
        }

        if (!$instance && $this->canCreateFromAbstractFactory($cName, $rName)) {
            $instance = $this->createFromAbstractFactory($cName, $rName);
        }

        if ($this->throwExceptionInCreate == true && $instance === false) {
            throw new Exception\ServiceNotFoundException(sprintf(
                'No valid instance was found for %s%s',
                $cName,
                ($rName ? '(alias: ' . $rName . ')' : '')
            ));
        }

        foreach ($this->initializers as $initializer) {
            if ($initializer instanceof InitializerInterface) {
                $initializer->initialize($instance, $this);
            } elseif (is_object($initializer) && is_callable($initializer)) {
                $initializer($instance, $this);
            } else {
                call_user_func($initializer, $instance, $this);
            }
        }

        return $instance;
    }

    /**
     * Determine if we can create an instance.
     *
     * @param  string|array $name
     * @param  bool         $checkAbstractFactories
     * @return bool
     */
    public function canCreate($name, $checkAbstractFactories = true)
    {
        if (is_array($name)) {
            list($cName, $rName) = $name;
        } else {
            $rName = $name;
            $cName = $this->canonicalizeName($rName);
        }

        if (
            isset($this->invokableClasses[$cName])
            || isset($this->factories[$cName])
            || isset($this->aliases[$cName])
            || isset($this->instances[$cName])
        ) {
            return true;
        }

        if ($checkAbstractFactories && $this->canCreateFromAbstractFactory($cName, $rName)) {
            return true;
        }

        return false;
    }

    /**
     * @param  string|array  $name
     * @param  bool          $checkAbstractFactories
     * @param  bool          $usePeeringServiceManagers
     * @return bool
     */
    public function has($name, $checkAbstractFactories = true, $usePeeringServiceManagers = true)
    {
        if (is_array($name)) {
            list($cName, $rName) = $name;
        } else {
            $rName = $name;
            $cName = $this->canonicalizeName($rName);
        }

        if ($this->canCreate(array($cName, $rName), $checkAbstractFactories)) {
            return true;
        }

        if ($usePeeringServiceManagers) {
            foreach ($this->peeringServiceManagers as $peeringServiceManager) {
                if ($peeringServiceManager->has($rName)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if we can create an instance from an abstract factory.
     *
     * @param  string $cName
     * @param  string $rName
     * @return bool
     */
    public function canCreateFromAbstractFactory($cName, $rName)
    {
        // check abstract factories
        foreach ($this->abstractFactories as $index => $abstractFactory) {
            // Support string abstract factory class names
            if (is_string($abstractFactory) && class_exists($abstractFactory, true)) {
                $this->abstractFactory[$index] = $abstractFactory = new $abstractFactory();
            }

            if (
                isset($this->pendingAbstractFactoryRequests[get_class($abstractFactory)])
                && $this->pendingAbstractFactoryRequests[get_class($abstractFactory)] == $rName
            ) {
                return false;
            }

            if ($abstractFactory->canCreateServiceWithName($this, $cName, $rName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param  string $alias
     * @param  string $nameOrAlias
     * @return ServiceManager
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\InvalidServiceNameException
     */
    public function setAlias($alias, $nameOrAlias)
    {
        if (!is_string($alias) || !is_string($nameOrAlias)) {
            throw new Exception\InvalidServiceNameException('Service or alias names must be strings.');
        }

        $cAlias = $this->canonicalizeName($alias);
        $nameOrAlias = $this->canonicalizeName($nameOrAlias);

        if ($alias == '' || $nameOrAlias == '') {
            throw new Exception\InvalidServiceNameException('Invalid service name alias');
        }

        if ($this->allowOverride === false && $this->has(array($cAlias, $alias), false)) {
            throw new Exception\InvalidServiceNameException('An alias by this name already exists');
        }

        $this->aliases[$cAlias] = $nameOrAlias;
        return $this;
    }

    /**
     * Determine if we have an alias
     *
     * @param  string $alias
     * @return bool
     */
    public function hasAlias($alias)
    {
        $alias = $this->canonicalizeName($alias);
        return (isset($this->aliases[$alias]));
    }

    /**
     * Create scoped service manager
     *
     * @param  string $peering
     * @return ServiceManager
     */
    public function createScopedServiceManager($peering = self::SCOPE_PARENT)
    {
        $scopedServiceManager = new ServiceManager();
        if ($peering == self::SCOPE_PARENT) {
            $scopedServiceManager->peeringServiceManagers[] = $this;
        }
        if ($peering == self::SCOPE_CHILD) {
            $this->peeringServiceManagers[] = $scopedServiceManager;
        }
        return $scopedServiceManager;
    }

    /**
     * Add a peering relationship
     *
     * @param  ServiceManager $manager
     * @param  string         $peering
     * @return ServiceManager
     */
    public function addPeeringServiceManager(ServiceManager $manager, $peering = self::SCOPE_PARENT)
    {
        if ($peering == self::SCOPE_PARENT) {
            $this->peeringServiceManagers[] = $manager;
        }
        if ($peering == self::SCOPE_CHILD) {
            $manager->peeringServiceManagers[] = $this;
        }
        return $this;
    }

    /**
     * Canonicalize name
     *
     * @param  string $name
     * @return string
     */
    protected function canonicalizeName($name)
    {
        if (isset($this->canonicalNames[$name])) {
            return $this->canonicalNames[$name];
        }

        // this is just for performance instead of using str_replace
        return $this->canonicalNames[$name] = strtolower(strtr($name, $this->canonicalNamesReplacements));
    }

    /**
     * Create service via callback
     *
     * @param  callable $callable
     * @param  string   $cName
     * @param  string   $rName
     * @throws Exception\ServiceNotCreatedException
     * @throws Exception\ServiceNotFoundException
     * @throws Exception\CircularDependencyFoundException
     * @return object
     */
    protected function createServiceViaCallback($callable, $cName, $rName)
    {
        static $circularDependencyResolver = array();
        $depKey = spl_object_hash($this) . '-' . $cName;

        if (isset($circularDependencyResolver[$depKey])) {
            $circularDependencyResolver = array();
            throw new Exception\CircularDependencyFoundException('Circular dependency for LazyServiceLoader was found for instance ' . $rName);
        }

        try {
            $circularDependencyResolver[$depKey] = true;
            $instance = call_user_func($callable, $this, $cName, $rName);
            unset($circularDependencyResolver[$depKey]);
        } catch (Exception\ServiceNotFoundException $e) {
            unset($circularDependencyResolver[$depKey]);
            throw $e;
        } catch (\Exception $e) {
            unset($circularDependencyResolver[$depKey]);
            throw new Exception\ServiceNotCreatedException(
                sprintf('An exception was raised while creating "%s"; no instance returned', $rName),
                $e->getCode(),
                $e
            );
        }
        if ($instance === null) {
            throw new Exception\ServiceNotCreatedException('The factory was called but did not return an instance.');
        }

        return $instance;
    }

    /**
     * Retrieve a keyed list of all registered services. Handy for debugging!
     *
     * @return array
     */
    public function getRegisteredServices()
    {
        return array(
            'invokableClasses' => array_keys($this->invokableClasses),
            'factories' => array_keys($this->factories),
            'aliases' => array_keys($this->aliases),
            'instances' => array_keys($this->instances),
        );
    }

    /**
     * Retrieve a keyed list of all canonical names. Handy for debugging!
     *
     * @return array
     */
    public function getCanonicalNames()
    {
        return $this->canonicalNames;
    }

    /**
     * Allows to override the canonical names lookup map with predefined
     * values.
     *
     * @param array $canonicalNames
     * @return ServiceManager
     */
    public function setCanonicalNames($canonicalNames)
    {
        $this->canonicalNames = $canonicalNames;

        return $this;
    }

    /**
     * Attempt to retrieve an instance via a peering manager
     *
     * @param  string $name
     * @return mixed
     */
    protected function retrieveFromPeeringManager($name)
    {
        foreach ($this->peeringServiceManagers as $peeringServiceManager) {
            if ($peeringServiceManager->has($name)) {
                return $peeringServiceManager->get($name);
            }
        }
        return null;
    }

    /**
     * Attempt to create an instance via an invokable class
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return null|\stdClass
     * @throws Exception\ServiceNotFoundException If resolved class does not exist
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];
        if (!class_exists($invokable)) {
            throw new Exception\ServiceNotFoundException(sprintf(
                '%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist',
                __METHOD__,
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : ''),
                $invokable
            ));
        }
        $instance = new $invokable;
        return $instance;
    }

    /**
     * Attempt to create an instance via a factory
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return mixed
     * @throws Exception\ServiceNotCreatedException If factory is not callable
     */
    protected function createFromFactory($canonicalName, $requestedName)
    {
        $factory = $this->factories[$canonicalName];
        if (is_string($factory) && class_exists($factory, true)) {
            $factory = new $factory;
            $this->factories[$canonicalName] = $factory;
        }
        if ($factory instanceof FactoryInterface) {
            $instance = $this->createServiceViaCallback(array($factory, 'createService'), $canonicalName, $requestedName);
        } elseif (is_callable($factory)) {
            $instance = $this->createServiceViaCallback($factory, $canonicalName, $requestedName);
        } else {
            throw new Exception\ServiceNotCreatedException(sprintf(
                'While attempting to create %s%s an invalid factory was registered for this instance type.',
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : '')
            ));
        }
        return $instance;
    }

    /**
     * Attempt to create an instance via an abstract factory
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return object|null
     * @throws Exception\ServiceNotCreatedException If abstract factory is not callable
     */
    protected function createFromAbstractFactory($canonicalName, $requestedName)
    {
        foreach ($this->abstractFactories as $index => $abstractFactory) {
            // support factories as strings
            if (is_string($abstractFactory) && class_exists($abstractFactory, true)) {
                $this->abstractFactories[$index] = $abstractFactory = new $abstractFactory;
            } elseif (!$abstractFactory instanceof AbstractFactoryInterface) {
                throw new Exception\ServiceNotCreatedException(sprintf(
                    'While attempting to create %s%s an abstract factory could not produce a valid instance.',
                    $canonicalName,
                    ($requestedName ? '(alias: ' . $requestedName . ')' : '')
                ));
            }
            try {
                $this->pendingAbstractFactoryRequests[get_class($abstractFactory)] = $requestedName;
                if ($abstractFactory->canCreateServiceWithName($this, $canonicalName, $requestedName)) {
                    $instance = $this->createServiceViaCallback(
                        array($abstractFactory, 'createServiceWithName'),
                        $canonicalName,
                        $requestedName
                    );
                    unset($this->pendingAbstractFactoryRequests[get_class($abstractFactory)]);
                } else {
                    $instance = false;
                }
            } catch (\Exception $e) {
                unset($this->pendingAbstractFactoryRequests[get_class($abstractFactory)]);
                throw new Exception\ServiceNotCreatedException(
                    sprintf(
                        'An abstract factory could not create an instance of %s%s.',
                        $canonicalName,
                        ($requestedName ? '(alias: ' . $requestedName . ')' : '')
                    ),
                    $e->getCode(),
                    $e
                );
            }
            if (is_object($instance)) {
                break;
            }
        }

        return $instance;
    }

    /**
     * Checks if the object has this class as one of its parents
     *
     * @see https://bugs.php.net/bug.php?id=53727
     * @see https://github.com/zendframework/zf2/pull/1807
     *
     * @param string $className
     * @param string $type
     * @return bool
     */
    protected static function isSubclassOf($className, $type)
    {
        if (is_subclass_of($className, $type)) {
            return true;
        }
        if (version_compare(PHP_VERSION, '5.3.7', '>=')) {
            return false;
        }
        if (!interface_exists($type)) {
            return false;
        }
        $r = new ReflectionClass($className);
        return $r->implementsInterface($type);
    }

    /**
     * Unregister a service
     *
     * Called when $allowOverride is true and we detect that a service being
     * added to the instance already exists. This will remove the duplicate
     * entry, and also any shared flags previously registered.
     *
     * @param  string $canonical
     * @return void
     */
    protected function unregisterService($canonical)
    {
        $types = array('invokableClasses', 'factories', 'aliases');
        foreach ($types as $type) {
            if (isset($this->{$type}[$canonical])) {
                unset($this->{$type}[$canonical]);
                break;
            }
        }

        if (isset($this->instances[$canonical])) {
            unset($this->instances[$canonical]);
        }

        if (isset($this->shared[$canonical])) {
            unset($this->shared[$canonical]);
        }
    }
}
