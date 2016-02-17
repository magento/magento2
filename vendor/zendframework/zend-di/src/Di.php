<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di;

use Closure;
use Zend\Di\Exception\RuntimeException as DiRuntimeException;
use Zend\ServiceManager\Exception\ExceptionInterface as ServiceManagerException;

/**
 * Dependency injector that can generate instances using class definitions and configured instance parameters
 */
class Di implements DependencyInjectionInterface
{
    /**
     * @var DefinitionList
     */
    protected $definitions = null;

    /**
     * @var InstanceManager
     */
    protected $instanceManager = null;

    /**
     * @var string
     */
    protected $instanceContext = array();

    /**
     * All the class dependencies [source][dependency]
     *
     * @var array
     */
    protected $currentDependencies = array();

    /**
     * All the dependenent aliases
     *
     * @var array
     */
    protected $currentAliasDependenencies = array();

    /**
     * All the class references [dependency][source]
     *
     * @var array
     */
    protected $references = array();

    /**
     * Resolve method policy
     *
     * EAGER: explore type preference or go through
     */
    const RESOLVE_EAGER = 1;

    /**
     * Resolve method policy
     *
     * STRICT: explore type preference or throw exception
     */
    const RESOLVE_STRICT = 2;

    /**
     *
     * use only specified parameters
     */
    const METHOD_IS_OPTIONAL = 0;

    /**
     *
     * resolve mode RESOLVE_EAGER
     */
    const METHOD_IS_AWARE = 1;

    /**
     *
     * resolve mode RESOLVE_EAGER | RESOLVE_STRICT
     */
    const METHOD_IS_CONSTRUCTOR = 3;

    /**
     *
     * resolve mode RESOLVE_EAGER | RESOLVE_STRICT
     */
    const METHOD_IS_INSTANTIATOR = 3;

    /**
     *
     * resolve mode RESOLVE_EAGER | RESOLVE_STRICT
     */
    const METHOD_IS_REQUIRED = 3;

    /**
     *
     * resolve mode RESOLVE_EAGER
     */
    const METHOD_IS_EAGER = 1;

    /**
     * Constructor
     *
     * @param null|DefinitionList  $definitions
     * @param null|InstanceManager $instanceManager
     * @param null|Config   $config
     */
    public function __construct(DefinitionList $definitions = null, InstanceManager $instanceManager = null, Config $config = null)
    {
        $this->definitions = ($definitions) ?: new DefinitionList(new Definition\RuntimeDefinition());
        $this->instanceManager = ($instanceManager) ?: new InstanceManager();

        if ($config) {
            $this->configure($config);
        }
    }

    /**
     * Provide a configuration object to configure this instance
     *
     * @param  Config $config
     * @return void
     */
    public function configure(Config $config)
    {
        $config->configure($this);
    }

    /**
     * @param  DefinitionList $definitions
     * @return self
     */
    public function setDefinitionList(DefinitionList $definitions)
    {
        $this->definitions = $definitions;

        return $this;
    }

    /**
     * @return DefinitionList
     */
    public function definitions()
    {
        return $this->definitions;
    }

    /**
     * Set the instance manager
     *
     * @param  InstanceManager $instanceManager
     * @return Di
     */
    public function setInstanceManager(InstanceManager $instanceManager)
    {
        $this->instanceManager = $instanceManager;

        return $this;
    }

    /**
     *
     * @return InstanceManager
     */
    public function instanceManager()
    {
        return $this->instanceManager;
    }

    /**
     * Utility method used to retrieve the class of a particular instance. This is here to allow extending classes to
     * override how class names are resolved
     *
     * @internal this method is used by the ServiceLocator\DependencyInjectorProxy class to interact with instances
     *           and is a hack to be used internally until a major refactor does not split the `resolveMethodParameters`. Do not
     *           rely on its functionality.
     * @param  object $instance
     * @return string
     */
    protected function getClass($instance)
    {
        return get_class($instance);
    }

    /**
     * @param $name
     * @param array $params
     * @param string $method
     * @return array
     */
    protected function getCallParameters($name, array $params, $method = "__construct")
    {
        $im    = $this->instanceManager;
        $class = $im->hasAlias($name) ? $im->getClassFromAlias($name) : $name;
        if ($this->definitions->hasClass($class)) {
            $callParameters = array();
            if ($this->definitions->hasMethod($class, $method)) {
                foreach ($this->definitions->getMethodParameters($class, $method) as $param) {
                    if (isset($params[$param[0]])) {
                        $callParameters[$param[0]] = $params[$param[0]];
                    }
                }
            }
            return $callParameters;
        }
        return $params;
    }

    /**
     * Lazy-load a class
     *
     * Attempts to load the class (or service alias) provided. If it has been
     * loaded before, the previous instance will be returned (unless the service
     * definition indicates shared instances should not be used).
     *
     * @param  string      $name   Class name or service alias
     * @param  null|array  $params Parameters to pass to the constructor
     * @return object|null
     */
    public function get($name, array $params = array())
    {
        array_push($this->instanceContext, array('GET', $name, null));

        $im = $this->instanceManager;

        $callParameters = $this->getCallParameters($name, $params);
        if ($callParameters) {
            $fastHash = $im->hasSharedInstanceWithParameters($name, $callParameters, true);
            if ($fastHash) {
                array_pop($this->instanceContext);
                return $im->getSharedInstanceWithParameters(null, array(), $fastHash);
            }
        }

        if ($im->hasSharedInstance($name, $callParameters)) {
            array_pop($this->instanceContext);
            return $im->getSharedInstance($name, $callParameters);
        }

        $config   = $im->getConfig($name);
        $instance = $this->newInstance($name, $params, $config['shared']);
        array_pop($this->instanceContext);

        return $instance;
    }

    /**
     * Retrieve a new instance of a class
     *
     * Forces retrieval of a discrete instance of the given class, using the
     * constructor parameters provided.
     *
     * @param  mixed                            $name     Class name or service alias
     * @param  array                            $params   Parameters to pass to the constructor
     * @param  bool                             $isShared
     * @return object|null
     * @throws Exception\ClassNotFoundException
     * @throws Exception\RuntimeException
     */
    public function newInstance($name, array $params = array(), $isShared = true)
    {
        // localize dependencies
        $definitions     = $this->definitions;
        $instanceManager = $this->instanceManager();

        if ($instanceManager->hasAlias($name)) {
            $class = $instanceManager->getClassFromAlias($name);
            $alias = $name;
        } else {
            $class = $name;
            $alias = null;
        }

        array_push($this->instanceContext, array('NEW', $class, $alias));

        if (!$definitions->hasClass($class)) {
            $aliasMsg = ($alias) ? '(specified by alias ' . $alias . ') ' : '';
            throw new Exception\ClassNotFoundException(
                'Class ' . $aliasMsg . $class . ' could not be located in provided definitions.'
            );
        }

        $instantiator     = $definitions->getInstantiator($class);
        $injectionMethods = array();
        $injectionMethods[$class] = $definitions->getMethods($class);

        foreach ($definitions->getClassSupertypes($class) as $supertype) {
            $injectionMethods[$supertype] = $definitions->getMethods($supertype);
        }

        if ($instantiator === '__construct') {
            $instance = $this->createInstanceViaConstructor($class, $params, $alias);
            if (array_key_exists('__construct', $injectionMethods)) {
                unset($injectionMethods['__construct']);
            }
        } elseif (is_callable($instantiator, false)) {
            $instance = $this->createInstanceViaCallback($instantiator, $params, $alias);
        } else {
            if (is_array($instantiator)) {
                $msg = sprintf(
                    'Invalid instantiator: %s::%s() is not callable.',
                    isset($instantiator[0]) ? $instantiator[0] : 'NoClassGiven',
                    isset($instantiator[1]) ? $instantiator[1] : 'NoMethodGiven'
                );
            } else {
                $msg = sprintf(
                    'Invalid instantiator of type "%s" for "%s".',
                    gettype($instantiator),
                    $name
                );
            }
            throw new Exception\RuntimeException($msg);
        }

        if ($isShared) {
            if ($callParameters = $this->getCallParameters($name, $params)) {
                $this->instanceManager->addSharedInstanceWithParameters($instance, $name, $callParameters);
            } else {
                $this->instanceManager->addSharedInstance($instance, $name);
            }
        }

        $this->handleInjectDependencies($instance, $injectionMethods, $params, $class, $alias, $name);

        array_pop($this->instanceContext);

        return $instance;
    }

    /**
     * Inject dependencies
     *
     * @param  object $instance
     * @param  array  $params
     * @return void
     */
    public function injectDependencies($instance, array $params = array())
    {
        $definitions = $this->definitions();
        $class = $this->getClass($instance);
        $injectionMethods = array(
            $class => ($definitions->hasClass($class)) ? $definitions->getMethods($class) : array()
        );
        $parent = $class;
        while ($parent = get_parent_class($parent)) {
            if ($definitions->hasClass($parent)) {
                $injectionMethods[$parent] = $definitions->getMethods($parent);
            }
        }
        foreach (class_implements($class) as $interface) {
            if ($definitions->hasClass($interface)) {
                $injectionMethods[$interface] = $definitions->getMethods($interface);
            }
        }
        $this->handleInjectDependencies($instance, $injectionMethods, $params, $class, null, null);
    }

    /**
     * @param object      $instance
     * @param array       $injectionMethods
     * @param array       $params
     * @param string|null $instanceClass
     * @param string|null$instanceAlias
     * @param  string                     $requestedName
     * @throws Exception\RuntimeException
     */
    protected function handleInjectDependencies($instance, $injectionMethods, $params, $instanceClass, $instanceAlias, $requestedName)
    {
        // localize dependencies
        $definitions     = $this->definitions;
        $instanceManager = $this->instanceManager();

        $calledMethods = array('__construct' => true);

        if ($injectionMethods) {
            foreach ($injectionMethods as $type => $typeInjectionMethods) {
                foreach ($typeInjectionMethods as $typeInjectionMethod => $methodRequirementType) {
                    if (!isset($calledMethods[$typeInjectionMethod])) {
                        if ($this->resolveAndCallInjectionMethodForInstance($instance, $typeInjectionMethod, $params, $instanceAlias, $methodRequirementType, $type)) {
                            $calledMethods[$typeInjectionMethod] = true;
                        }
                    }
                }
            }

            if ($requestedName) {
                $instanceConfig = $instanceManager->getConfig($requestedName);

                if ($instanceConfig['injections']) {
                    $objectsToInject = $methodsToCall = array();
                    foreach ($instanceConfig['injections'] as $injectName => $injectValue) {
                        if (is_int($injectName) && is_string($injectValue)) {
                            $objectsToInject[] = $this->get($injectValue, $params);
                        } elseif (is_string($injectName) && is_array($injectValue)) {
                            if (is_string(key($injectValue))) {
                                $methodsToCall[] = array('method' => $injectName, 'args' => $injectValue);
                            } else {
                                foreach ($injectValue as $methodCallArgs) {
                                    $methodsToCall[] = array('method' => $injectName, 'args' => $methodCallArgs);
                                }
                            }
                        } elseif (is_object($injectValue)) {
                            $objectsToInject[] = $injectValue;
                        } elseif (is_int($injectName) && is_array($injectValue)) {
                            throw new Exception\RuntimeException(
                                'An injection was provided with a keyed index and an array of data, try using'
                                . ' the name of a particular method as a key for your injection data.'
                            );
                        }
                    }
                    if ($objectsToInject) {
                        foreach ($objectsToInject as $objectToInject) {
                            $calledMethods = array('__construct' => true);
                            foreach ($injectionMethods as $type => $typeInjectionMethods) {
                                foreach ($typeInjectionMethods as $typeInjectionMethod => $methodRequirementType) {
                                    if (!isset($calledMethods[$typeInjectionMethod])) {
                                        $methodParams = $definitions->getMethodParameters($type, $typeInjectionMethod);
                                        if ($methodParams) {
                                            foreach ($methodParams as $methodParam) {
                                                $objectToInjectClass = $this->getClass($objectToInject);
                                                if ($objectToInjectClass == $methodParam[1] || is_subclass_of($objectToInjectClass, $methodParam[1])) {
                                                    if ($this->resolveAndCallInjectionMethodForInstance($instance, $typeInjectionMethod, array($methodParam[0] => $objectToInject), $instanceAlias, self::METHOD_IS_REQUIRED, $type)) {
                                                        $calledMethods[$typeInjectionMethod] = true;
                                                    }
                                                    continue 3;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($methodsToCall) {
                        foreach ($methodsToCall as $methodInfo) {
                            $this->resolveAndCallInjectionMethodForInstance($instance, $methodInfo['method'], $methodInfo['args'], $instanceAlias, self::METHOD_IS_REQUIRED, $instanceClass);
                        }
                    }
                }
            }
        }
    }

    /**
     * Retrieve a class instance based on class name
     *
     * Any parameters provided will be used as constructor arguments. If any
     * given parameter is a DependencyReference object, it will be fetched
     * from the container so that the instance may be injected.
     *
     * @param  string      $class
     * @param  array       $params
     * @param  string|null $alias
     * @return object
     */
    protected function createInstanceViaConstructor($class, $params, $alias = null)
    {
        $callParameters = array();
        if ($this->definitions->hasMethod($class, '__construct')) {
            $callParameters = $this->resolveMethodParameters($class, '__construct', $params, $alias, self::METHOD_IS_CONSTRUCTOR, true);
        }

        if (!class_exists($class)) {
            if (interface_exists($class)) {
                throw new Exception\ClassNotFoundException(sprintf(
                    'Cannot instantiate interface "%s"',
                    $class
                ));
            }
            throw new Exception\ClassNotFoundException(sprintf(
                'Class "%s" does not exist; cannot instantiate',
                $class
            ));
        }

        // Hack to avoid Reflection in most common use cases
        switch (count($callParameters)) {
            case 0:
                return new $class();
            case 1:
                return new $class($callParameters[0]);
            case 2:
                return new $class($callParameters[0], $callParameters[1]);
            case 3:
                return new $class($callParameters[0], $callParameters[1], $callParameters[2]);
            default:
                $r = new \ReflectionClass($class);

                return $r->newInstanceArgs($callParameters);
        }
    }

    /**
     * Get an object instance from the defined callback
     *
     * @param  callable                           $callback
     * @param  array                              $params
     * @param  string                             $alias
     * @return object
     * @throws Exception\InvalidCallbackException
     * @throws Exception\RuntimeException
     */
    protected function createInstanceViaCallback($callback, $params, $alias)
    {
        if (!is_callable($callback)) {
            throw new Exception\InvalidCallbackException('An invalid constructor callback was provided');
        }

        if (is_array($callback)) {
            $class = (is_object($callback[0])) ? $this->getClass($callback[0]) : $callback[0];
            $method = $callback[1];
        } elseif (is_string($callback) && strpos($callback, '::') !== false) {
            list($class, $method) = explode('::', $callback, 2);
        } else {
            throw new Exception\RuntimeException('Invalid callback type provided to ' . __METHOD__);
        }

        $callParameters = array();
        if ($this->definitions->hasMethod($class, $method)) {
            $callParameters = $this->resolveMethodParameters($class, $method, $params, $alias, self::METHOD_IS_INSTANTIATOR, true);
        }

        return call_user_func_array($callback, $callParameters);
    }

    /**
     * This parameter will handle any injection methods and resolution of
     * dependencies for such methods
     *
     * @param  object      $instance
     * @param  string      $method
     * @param  array       $params
     * @param  string      $alias
     * @param  bool        $methodRequirementType
     * @param  string|null $methodClass
     * @return bool
     */
    protected function resolveAndCallInjectionMethodForInstance($instance, $method, $params, $alias, $methodRequirementType, $methodClass = null)
    {
        $methodClass = ($methodClass) ?: $this->getClass($instance);
        $callParameters = $this->resolveMethodParameters($methodClass, $method, $params, $alias, $methodRequirementType);
        if ($callParameters == false) {
            return false;
        }
        if ($callParameters !== array_fill(0, count($callParameters), null)) {
            call_user_func_array(array($instance, $method), $callParameters);

            return true;
        }

        return false;
    }

    /**
     * Resolve parameters referencing other services
     *
     * @param  string                                $class
     * @param  string                                $method
     * @param  array                                 $callTimeUserParams
     * @param  string                                $alias
     * @param  int|bool                              $methodRequirementType
     * @param  bool                                  $isInstantiator
     * @throws Exception\MissingPropertyException
     * @throws Exception\CircularDependencyException
     * @return array
     */
    protected function resolveMethodParameters($class, $method, array $callTimeUserParams, $alias, $methodRequirementType, $isInstantiator = false)
    {
        //for BC
        if (is_bool($methodRequirementType)) {
            if ($isInstantiator) {
                $methodRequirementType = Di::METHOD_IS_INSTANTIATOR;
            } elseif ($methodRequirementType) {
                $methodRequirementType = Di::METHOD_IS_REQUIRED;
            } else {
                $methodRequirementType = Di::METHOD_IS_OPTIONAL;
            }
        }
        // parameters for this method, in proper order, to be returned
        $resolvedParams = array();

        // parameter requirements from the definition
        $injectionMethodParameters = $this->definitions->getMethodParameters($class, $method);

        // computed parameters array
        $computedParams = array(
            'value'     => array(),
            'retrieval' => array(),
            'optional'  => array()
        );

        // retrieve instance configurations for all contexts
        $iConfig = array();
        $aliases = $this->instanceManager->getAliases();

        // for the alias in the dependency tree
        if ($alias && $this->instanceManager->hasConfig($alias)) {
            $iConfig['thisAlias'] = $this->instanceManager->getConfig($alias);
        }

        // for the current class in the dependency tree
        if ($this->instanceManager->hasConfig($class)) {
            $iConfig['thisClass'] = $this->instanceManager->getConfig($class);
        }

        // for the parent class, provided we are deeper than one node
        if (isset($this->instanceContext[0])) {
            list($requestedClass, $requestedAlias) = ($this->instanceContext[0][0] == 'NEW')
                ? array($this->instanceContext[0][1], $this->instanceContext[0][2])
                : array($this->instanceContext[1][1], $this->instanceContext[1][2]);
        } else {
            $requestedClass = $requestedAlias = null;
        }

        if ($requestedClass != $class && $this->instanceManager->hasConfig($requestedClass)) {
            $iConfig['requestedClass'] = $this->instanceManager->getConfig($requestedClass);

            if (array_key_exists('parameters', $iConfig['requestedClass'])) {
                $newParameters = array();

                foreach ($iConfig['requestedClass']['parameters'] as $name => $parameter) {
                    $newParameters[$requestedClass.'::'.$method.'::'.$name] = $parameter;
                }

                $iConfig['requestedClass']['parameters'] = $newParameters;
            }

            if ($requestedAlias) {
                $iConfig['requestedAlias'] = $this->instanceManager->getConfig($requestedAlias);
            }
        }

        // This is a 2 pass system for resolving parameters
        // first pass will find the sources, the second pass will order them and resolve lookups if they exist
        // MOST methods will only have a single parameters to resolve, so this should be fast

        foreach ($injectionMethodParameters as $fqParamPos => $info) {
            list($name, $type, $isRequired) = $info;

            $fqParamName = substr_replace($fqParamPos, ':' . $info[0], strrpos($fqParamPos, ':'));

            // PRIORITY 1 - consult user provided parameters
            if (isset($callTimeUserParams[$fqParamPos]) || isset($callTimeUserParams[$name])) {
                if (isset($callTimeUserParams[$fqParamPos])) {
                    $callTimeCurValue =& $callTimeUserParams[$fqParamPos];
                } elseif (isset($callTimeUserParams[$fqParamName])) {
                    $callTimeCurValue =& $callTimeUserParams[$fqParamName];
                } else {
                    $callTimeCurValue =& $callTimeUserParams[$name];
                }

                if ($type !== false && is_string($callTimeCurValue)) {
                    if ($this->instanceManager->hasAlias($callTimeCurValue)) {
                        // was an alias provided?
                        $computedParams['retrieval'][$fqParamPos] = array(
                            $callTimeUserParams[$name],
                            $this->instanceManager->getClassFromAlias($callTimeCurValue)
                        );
                    } elseif ($this->definitions->hasClass($callTimeUserParams[$name])) {
                        // was a known class provided?
                        $computedParams['retrieval'][$fqParamPos] = array(
                            $callTimeCurValue,
                            $callTimeCurValue
                        );
                    } else {
                        // must be a value
                        $computedParams['value'][$fqParamPos] = $callTimeCurValue;
                    }
                } else {
                    // int, float, null, object, etc
                    $computedParams['value'][$fqParamPos] = $callTimeCurValue;
                }
                unset($callTimeCurValue);
                continue;
            }

            // PRIORITY 2 -specific instance configuration (thisAlias) - this alias
            // PRIORITY 3 -THEN specific instance configuration (thisClass) - this class
            // PRIORITY 4 -THEN specific instance configuration (requestedAlias) - requested alias
            // PRIORITY 5 -THEN specific instance configuration (requestedClass) - requested class

            foreach (array('thisAlias', 'thisClass', 'requestedAlias', 'requestedClass') as $thisIndex) {
                // check the provided parameters config
                if (isset($iConfig[$thisIndex]['parameters'][$fqParamPos])
                    || isset($iConfig[$thisIndex]['parameters'][$fqParamName])
                    || isset($iConfig[$thisIndex]['parameters'][$name])) {
                    if (isset($iConfig[$thisIndex]['parameters'][$fqParamPos])) {
                        $iConfigCurValue =& $iConfig[$thisIndex]['parameters'][$fqParamPos];
                    } elseif (isset($iConfig[$thisIndex]['parameters'][$fqParamName])) {
                        $iConfigCurValue =& $iConfig[$thisIndex]['parameters'][$fqParamName];
                    } else {
                        $iConfigCurValue =& $iConfig[$thisIndex]['parameters'][$name];
                    }

                    if ($type === false && is_string($iConfigCurValue)) {
                        $computedParams['value'][$fqParamPos] = $iConfigCurValue;
                    } elseif (is_string($iConfigCurValue)
                        && isset($aliases[$iConfigCurValue])) {
                        $computedParams['retrieval'][$fqParamPos] = array(
                            $iConfig[$thisIndex]['parameters'][$name],
                            $this->instanceManager->getClassFromAlias($iConfigCurValue)
                        );
                    } elseif (is_string($iConfigCurValue)
                        && $this->definitions->hasClass($iConfigCurValue)) {
                        $computedParams['retrieval'][$fqParamPos] = array(
                            $iConfigCurValue,
                            $iConfigCurValue
                        );
                    } elseif (is_object($iConfigCurValue)
                        && $iConfigCurValue instanceof Closure
                        && $type !== 'Closure') {
                        /* @var $iConfigCurValue Closure */
                        $computedParams['value'][$fqParamPos] = $iConfigCurValue();
                    } else {
                        $computedParams['value'][$fqParamPos] = $iConfigCurValue;
                    }
                    unset($iConfigCurValue);
                    continue 2;
                }
            }

            // PRIORITY 6 - globally preferred implementations

            // next consult alias level preferred instances
            // RESOLVE_EAGER wants to inject the cross-cutting concerns.
            // If you want to retrieve an instance from TypePreferences,
            // use AwareInterface or specify the method requirement option METHOD_IS_EAGER at ClassDefinition
            if ($methodRequirementType & self::RESOLVE_EAGER) {
                if ($alias && $this->instanceManager->hasTypePreferences($alias)) {
                    $pInstances = $this->instanceManager->getTypePreferences($alias);
                    foreach ($pInstances as $pInstance) {
                        if (is_object($pInstance)) {
                            $computedParams['value'][$fqParamPos] = $pInstance;
                            continue 2;
                        }
                        $pInstanceClass = ($this->instanceManager->hasAlias($pInstance)) ?
                             $this->instanceManager->getClassFromAlias($pInstance) : $pInstance;
                        if ($pInstanceClass === $type || is_subclass_of($pInstanceClass, $type)) {
                            $computedParams['retrieval'][$fqParamPos] = array($pInstance, $pInstanceClass);
                            continue 2;
                        }
                    }
                }

                // next consult class level preferred instances
                if ($type && $this->instanceManager->hasTypePreferences($type)) {
                    $pInstances = $this->instanceManager->getTypePreferences($type);
                    foreach ($pInstances as $pInstance) {
                        if (is_object($pInstance)) {
                            $computedParams['value'][$fqParamPos] = $pInstance;
                            continue 2;
                        }
                        $pInstanceClass = ($this->instanceManager->hasAlias($pInstance)) ?
                             $this->instanceManager->getClassFromAlias($pInstance) : $pInstance;
                        if ($pInstanceClass === $type || is_subclass_of($pInstanceClass, $type)) {
                            $computedParams['retrieval'][$fqParamPos] = array($pInstance, $pInstanceClass);
                            continue 2;
                        }
                    }
                }
            }
            if (!$isRequired) {
                $computedParams['optional'][$fqParamPos] = true;
            }

            if ($type && $isRequired && ($methodRequirementType & self::RESOLVE_EAGER)) {
                $computedParams['retrieval'][$fqParamPos] = array($type, $type);
            }
        }

        $index = 0;
        foreach ($injectionMethodParameters as $fqParamPos => $value) {
            $name = $value[0];

            if (isset($computedParams['value'][$fqParamPos])) {
                // if there is a value supplied, use it
                $resolvedParams[$index] = $computedParams['value'][$fqParamPos];
            } elseif (isset($computedParams['retrieval'][$fqParamPos])) {
                // detect circular dependencies! (they can only happen in instantiators)
                if ($isInstantiator && in_array($computedParams['retrieval'][$fqParamPos][1], $this->currentDependencies)
                    && (!isset($alias) || in_array($computedParams['retrieval'][$fqParamPos][0], $this->currentAliasDependenencies))
                ) {
                    $msg = "Circular dependency detected: $class depends on {$value[1]} and viceversa";
                    if (isset($alias)) {
                        $msg .= " (Aliased as $alias)";
                    }
                    throw new Exception\CircularDependencyException($msg);
                }

                array_push($this->currentDependencies, $class);
                if (isset($alias)) {
                    array_push($this->currentAliasDependenencies, $alias);
                }

                $dConfig = $this->instanceManager->getConfig($computedParams['retrieval'][$fqParamPos][0]);

                try {
                    if ($dConfig['shared'] === false) {
                        $resolvedParams[$index] = $this->newInstance($computedParams['retrieval'][$fqParamPos][0], $callTimeUserParams, false);
                    } else {
                        $resolvedParams[$index] = $this->get($computedParams['retrieval'][$fqParamPos][0], $callTimeUserParams);
                    }
                } catch (DiRuntimeException $e) {
                    if ($methodRequirementType & self::RESOLVE_STRICT) {
                        //finally ( be aware to do at the end of flow)
                        array_pop($this->currentDependencies);
                        if (isset($alias)) {
                            array_pop($this->currentAliasDependenencies);
                        }
                        // if this item was marked strict,
                        // plus it cannot be resolve, and no value exist, bail out
                        throw new Exception\MissingPropertyException(
                            sprintf(
                                'Missing %s for parameter ' . $name . ' for ' . $class . '::' . $method,
                                (($value[0] === null) ? 'value' : 'instance/object')
                            ),
                            $e->getCode(),
                            $e
                        );
                    } else {
                        //finally ( be aware to do at the end of flow)
                        array_pop($this->currentDependencies);
                        if (isset($alias)) {
                            array_pop($this->currentAliasDependenencies);
                        }
                        return false;
                    }
                } catch (ServiceManagerException $e) {
                    // Zend\ServiceManager\Exception\ServiceNotCreatedException
                    if ($methodRequirementType & self::RESOLVE_STRICT) {
                        //finally ( be aware to do at the end of flow)
                        array_pop($this->currentDependencies);
                        if (isset($alias)) {
                            array_pop($this->currentAliasDependenencies);
                        }
                        // if this item was marked strict,
                        // plus it cannot be resolve, and no value exist, bail out
                        throw new Exception\MissingPropertyException(
                            sprintf(
                                'Missing %s for parameter ' . $name . ' for ' . $class . '::' . $method,
                                (($value[0] === null) ? 'value' : 'instance/object')
                            ),
                            $e->getCode(),
                            $e
                        );
                    } else {
                        //finally ( be aware to do at the end of flow)
                        array_pop($this->currentDependencies);
                        if (isset($alias)) {
                            array_pop($this->currentAliasDependenencies);
                        }
                        return false;
                    }
                }
                array_pop($this->currentDependencies);
                if (isset($alias)) {
                    array_pop($this->currentAliasDependenencies);
                }
            } elseif (!array_key_exists($fqParamPos, $computedParams['optional'])) {
                if ($methodRequirementType & self::RESOLVE_STRICT) {
                    // if this item was not marked as optional,
                    // plus it cannot be resolve, and no value exist, bail out
                    throw new Exception\MissingPropertyException(sprintf(
                        'Missing %s for parameter ' . $name . ' for ' . $class . '::' . $method,
                        (($value[0] === null) ? 'value' : 'instance/object')
                    ));
                } else {
                    return false;
                }
            } else {
                $resolvedParams[$index] = $value[3];
            }

            $index++;
        }

        return $resolvedParams; // return ordered list of parameters
    }

    /**
     * Checks if the object has this class as one of its parents
     *
     * @see https://bugs.php.net/bug.php?id=53727
     * @see https://github.com/zendframework/zf2/pull/1807
     *
     * @deprecated since zf 2.3 requires PHP >= 5.3.23
     *
     * @param string $className
     * @param $type
     * @return bool
     */
    protected static function isSubclassOf($className, $type)
    {
        return is_subclass_of($className, $type);
    }
}
