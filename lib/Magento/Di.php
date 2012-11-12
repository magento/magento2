<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Di
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Zend\Di\Exception;

class Magento_Di extends Zend\Di\Di
{
    /**
     * @var array
     */
    protected $_cachedInstances;

    /**
     * @var array
     */
    protected $_baseDefinitions = array();

    /**
     * Retrieve a new instance of a class
     *
     * Forces retrieval of a discrete instance of the given class, using the
     * constructor parameters provided.
     *
     * @param mixed $name
     * @param array $parameters
     * @param bool $isShared
     * @return null|object
     * @throws Zend\Di\Exception\RuntimeException
     * @throws Zend\Di\Exception\ClassNotFoundException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @todo Need to refactor this method
     */
    public function newInstance($name, array $parameters = array(), $isShared = true)
    {
        // localize dependencies
        $definitions = $this->definitions;

        if (!$this->definitions()->hasClass($name)) {
            array_push($this->instanceContext, array('NEW', $name, $name));

            if (!$this->_cachedInstances) {
                $this->_cachedInstances = array(
                    'eventManager' => $this->get('Mage_Core_Model_Event_Manager'),
                    'cache'        => $this->get('Mage_Core_Model_Cache'),
                );
            }
            if (preg_match('/\w*_\w*\_Model/', $name)) {
                $instance = new $name(
                    $this->_cachedInstances['eventManager'],
                    $this->_cachedInstances['cache'],
                    null,
                    null,
                    $parameters
                );
            } else if (preg_match('/\w*_\w*\_Block/', $name)) {
                if (!isset($this->_cachedInstances['request'])) {
                    $this->_cachedInstances['request']         = $this->get('Mage_Core_Controller_Request_Http');
                    $this->_cachedInstances['layout']          = $this->get('Mage_Core_Model_Layout');
                    $this->_cachedInstances['translate']       = $this->get('Mage_Core_Model_Translate');
                    $this->_cachedInstances['design']          = $this->get('Mage_Core_Model_Design_Package');
                    $this->_cachedInstances['session']         = $this->get('Mage_Core_Model_Session');
                    $this->_cachedInstances['storeConfig']     = $this->get('Mage_Core_Model_Store_Config');
                    $this->_cachedInstances['frontController'] = $this->get('Mage_Core_Controller_Varien_Front');
                }
                $instance = new $name(
                    $this->_cachedInstances['request'],
                    $this->_cachedInstances['layout'],
                    $this->_cachedInstances['eventManager'],
                    $this->_cachedInstances['translate'],
                    $this->_cachedInstances['cache'],
                    $this->_cachedInstances['design'],
                    $this->_cachedInstances['session'],
                    $this->_cachedInstances['storeConfig'],
                    $this->_cachedInstances['frontController'],
                    $parameters
                );
            } else {
                $instance = new $name();
            }
            if ($isShared) {
                if ($parameters) {
                    $this->instanceManager->addSharedInstanceWithParameters($instance, $name, $parameters);
                } else {
                    $this->instanceManager->addSharedInstance($instance, $name);
                }
            }
            array_pop($this->instanceContext);
            return $instance;
        }

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

        $instantiator = $definitions->getInstantiator($class);

        if ($instantiator === '__construct') {
            $instance = $this->createInstanceViaConstructor($class, $parameters, $alias);
        } elseif (is_callable($instantiator, false)) {
            $instance = $this->createInstanceViaCallback($instantiator, $parameters, $alias);
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
            if ($parameters) {
                $this->instanceManager->addSharedInstanceWithParameters($instance, $name, $parameters);
            } else {
                $this->instanceManager->addSharedInstance($instance, $name);
            }
        }

        array_pop($this->instanceContext);
        return $instance;
    }

    /**
     * Retrieve a class instance based on class name
     *
     * Any parameters provided will be used as constructor arguments. If any
     * given parameter is a DependencyReference object, it will be fetched
     * from the container so that the instance may be injected.
     *
     * @param string $class
     * @param array $params
     * @param string|null $alias
     * @return object
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @todo Need to refactor this method
     */
    protected function createInstanceViaConstructor($class, $params, $alias = null)
    {
        $callParameters = array();
        if ($this->definitions->hasMethodParameters($class, '__construct')) {
            $callParameters = $this->resolveMethodParameters($class, '__construct', $params, $alias, true, true);
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
            case 4:
                return new $class($callParameters[0], $callParameters[1], $callParameters[2], $callParameters[3]);
            case 5:
                return new $class($callParameters[0], $callParameters[1], $callParameters[2], $callParameters[3],
                    $callParameters[4]
                );
            case 6:
                return new $class($callParameters[0], $callParameters[1], $callParameters[2], $callParameters[3],
                    $callParameters[4], $callParameters[5]
                );
            case 7:
                return new $class($callParameters[0], $callParameters[1], $callParameters[2], $callParameters[3],
                    $callParameters[4], $callParameters[5], $callParameters[6]
                );
            case 8:
                return new $class($callParameters[0], $callParameters[1], $callParameters[2], $callParameters[3],
                    $callParameters[4], $callParameters[5], $callParameters[6], $callParameters[7]
                );
            case 9:
                return new $class($callParameters[0], $callParameters[1], $callParameters[2], $callParameters[3],
                    $callParameters[4], $callParameters[5], $callParameters[6], $callParameters[7], $callParameters[8]
                );
            case 10:
                return new $class($callParameters[0], $callParameters[1], $callParameters[2], $callParameters[3],
                    $callParameters[4], $callParameters[5], $callParameters[6], $callParameters[7], $callParameters[8],
                    $callParameters[9]
                );

            default:
                $reflection = new \ReflectionClass($class);
                return $reflection->newInstanceArgs($callParameters);
        }
    }

    /**
     * Resolve parameters referencing other services
     *
     * @param string $class
     * @param string $method
     * @param array $callTimeUserParams
     * @param string $alias
     * @param bool $methodIsRequired
     * @param bool $isInstantiator
     * @return array|bool
     * @throws Zend\Di\Exception\MissingPropertyException
     * @throws Zend\Di\Exception\CircularDependencyException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @todo Need to refactor this method
     */
    protected function resolveMethodParameters($class, $method, array $callTimeUserParams, $alias, $methodIsRequired,
        $isInstantiator = false
    ) {
        // parameters for this method, in proper order, to be returned
        $resolvedParams = array();

        // parameter requirements from the definition
        $methodParameters = $this->definitions->getMethodParameters($class, $method);

        /** Magento  */
        $callTimeParamNames = array_keys($callTimeUserParams);
        $isPositional = false;
        $parameterNames = array();

        foreach ($methodParameters as $param) {
            $parameterNames[] = $param[0];
        }

        foreach ($callTimeParamNames as $name) {
            if (is_numeric($name)) {
                $isPositional = true;
                $callTimeUserParams[$parameterNames[$name]] = $callTimeUserParams[$name];
            }
        }

        if (!$isPositional) {
            if (count($callTimeUserParams)
                && !isset($callTimeUserParams['data'])
            ) {
                if (in_array('data', $parameterNames)) {
                    $intersection = array_intersect($callTimeParamNames, $parameterNames);
                    if (!$intersection) {
                        $callTimeUserParams = array('data' => $callTimeUserParams);
                    }
                }
            }
        }

        // computed parameters array
        $computedParams = array(
            'value'    => array(),
            'required' => array(),
            'optional' => array()
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
            if ($requestedAlias) {
                $iConfig['requestedAlias'] = $this->instanceManager->getConfig($requestedAlias);
            }
        }

        // This is a 2 pass system for resolving parameters
        // first pass will find the sources, the second pass will order them and resolve lookups if they exist
        // MOST methods will only have a single parameters to resolve, so this should be fast

        foreach ($methodParameters as $fqParamPos => $info) {
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

                if ($type && is_string($callTimeCurValue)) {
                    if ($this->instanceManager->hasAlias($callTimeCurValue)) {
                        // was an alias provided?
                        $computedParams['required'][$fqParamPos] = array(
                            $callTimeUserParams[$name],
                            $this->instanceManager->getClassFromAlias($callTimeCurValue)
                        );
                    } elseif ($this->definitions->hasClass($callTimeUserParams[$name])) {
                        // was a known class provided?
                        $computedParams['required'][$fqParamPos] = array(
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
                        $computedParams['required'][$fqParamPos] = array(
                            $iConfig[$thisIndex]['parameters'][$name],
                            $this->instanceManager->getClassFromAlias($iConfigCurValue)
                        );
                    } elseif (is_string($iConfigCurValue)
                        && $this->definitions->hasClass($iConfigCurValue)) {
                        $computedParams['required'][$fqParamPos] = array(
                            $iConfigCurValue,
                            $iConfigCurValue
                        );
                    } elseif (is_object($iConfigCurValue)
                        && $iConfigCurValue instanceof Closure
                        && $type !== 'Closure') {
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
            if ($alias && $this->instanceManager->hasTypePreferences($alias)) {
                $pInstances = $this->instanceManager->getTypePreferences($alias);
                foreach ($pInstances as $pInstance) {
                    if (is_object($pInstance)) {
                        $computedParams['value'][$fqParamPos] = $pInstance;
                        continue 2;
                    }
                    $pInstanceClass = ($this->instanceManager->hasAlias($pInstance)) ?
                        $this->instanceManager->getClassFromAlias($pInstance) : $pInstance;
                    if ($pInstanceClass === $type || $this->isSubclassOf($pInstanceClass, $type)) {
                        $computedParams['required'][$fqParamPos] = array($pInstance, $pInstanceClass);
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
                    if ($pInstanceClass === $type || $this->isSubclassOf($pInstanceClass, $type)) {
                        $computedParams['required'][$fqParamPos] = array($pInstance, $pInstanceClass);
                        continue 2;
                    }
                }
            }

            if (!$isRequired) {
                $computedParams['optional'][$fqParamPos] = true;
            }

            if ($type && $isRequired && $methodIsRequired) {
                $computedParams['required'][$fqParamPos] = array($type, $type);
            }

        }

        $index = 0;
        foreach ($methodParameters as $fqParamPos => $value) {
            $name = $value[0];
            $defaultValue = $value[3];

            if (isset($computedParams['value'][$fqParamPos])) {

                // if there is a value supplied, use it
                $resolvedParams[$index] = $computedParams['value'][$fqParamPos];

            } elseif (isset($computedParams['required'][$fqParamPos])) {

                // detect circular dependencies! (they can only happen in instantiators)
                if ($isInstantiator
                    && in_array($computedParams['required'][$fqParamPos][1], $this->currentDependencies)) {
                    throw new Exception\CircularDependencyException(
                        "Circular dependency detected: $class depends on {$value[1]} and vice versa"
                    );
                }
                array_push($this->currentDependencies, $class);
                $dConfig = $this->instanceManager->getConfig($computedParams['required'][$fqParamPos][0]);
                if ($dConfig['shared'] === false) {
                    $resolvedParams[$index]
                        = $this->newInstance($computedParams['required'][$fqParamPos][0], $callTimeUserParams, false);
                } else {
                    $resolvedParams[$index] = $this->get($computedParams['required'][$fqParamPos][0]);
                }

                array_pop($this->currentDependencies);

            } elseif (!array_key_exists($fqParamPos, $computedParams['optional'])) {

                if ($methodIsRequired) {
                    // if this item was not marked as optional,
                    // plus it cannot be resolve, and no value exist, bail out
                    throw new Exception\MissingPropertyException(sprintf(
                        'Missing %s for parameter ' . $name . ' for ' . $class . '::' . $method,
                        (($value[0] === null) ? 'value' : 'instance/object' )
                    ));
                } else {
                    return false;
                }

            } else {
                $resolvedParams[$index] = $defaultValue;
            }

            $index++;
        }

        return $resolvedParams; // return ordered list of parameters
    }
}
