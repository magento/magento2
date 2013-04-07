<?php
/**
 * Magento object manager. Responsible for instantiating objects taking itno account:
 * - constructor arguments (using configured, and provided parameters)
 * - class instances life style (singleton, transient)
 * - interface preferences
 *
 * Intentionally contains multiple concerns for optimum performance
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_ObjectManager_ObjectManager implements Magento_ObjectManager
{
    /**
     * Class definitions
     *
     * @var Magento_ObjectManager_Definition
     */
    protected $_definitions;

    /**
     * List of configured arguments
     *
     * @var array
     */
    protected $_arguments = array();

    /**
     * List of not shared classes
     *
     * @var array
     */
    protected $_nonShared = array();

    /**
     * Interface preferences
     *
     * @var array
     */
    protected $_preferences = array();

    /**
     * List of classes being created
     *
     * @var array
     */
    protected $_creationStack = array();

    /**
     * List of shared instances
     *
     * @var array
     */
    protected $_sharedInstances = array();

    /**
     * @param Magento_ObjectManager_Definition $definitions
     * @param array $configuration
     * @param array $sharedInstances
     */
    public function __construct(
        Magento_ObjectManager_Definition $definitions = null,
        array $configuration = array(),
        array $sharedInstances = array()
    ) {
        $this->_definitions = $definitions ?: new Magento_ObjectManager_Definition_Runtime();
        $this->_sharedInstances = $sharedInstances;
        $this->_sharedInstances['Magento_ObjectManager'] = $this;
        $this->configure($configuration);
    }

    /**
     * Resolve constructor arguments
     *
     * @param string $className
     * @param array $parameters
     * @param array $arguments
     * @return array
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws BadMethodCallException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _resolveArguments($className, array $parameters, array $arguments = array())
    {
        $resolvedArguments = array();
        if (isset($this->_arguments[$className])) {
            $arguments = array_replace($this->_arguments[$className], $arguments);
        }
        $paramPosition = 0;
        foreach ($parameters as $parameter) {
            list($paramName, $paramType, $paramRequired, $paramDefault) = $parameter;
            $argument = null;
            $hasPositionalArg = array_key_exists($paramPosition, $arguments);
            $hasNamedArg = array_key_exists($paramName, $arguments);
            if ($hasPositionalArg && $hasNamedArg) {
                throw new InvalidArgumentException(
                    'Ambiguous argument $' . $paramName . ': positional and named binding is used at the same time.'
                );
            }
            if ($hasPositionalArg) {
                $argument = $arguments[$paramPosition];
            } else if ($hasNamedArg) {
                $argument = $arguments[$paramName];
            } else if ($paramRequired) {
                if (!$paramType) {
                    throw new BadMethodCallException(
                        'Missing required argument $' . $paramName . ' for ' . $className . '.'
                    );
                }
                $argument = $paramType;
            } else {
                $argument = $paramDefault;
            }
            if ($paramRequired && $paramType && !is_object($argument)) {
                if (isset($this->_creationStack[$argument])) {
                    throw new LogicException(
                        'Circular dependency: ' . $argument . ' depends on ' . $className . ' and viceversa.'
                    );
                }

                $this->_creationStack[$className] = 1;
                $argument = isset($this->_nonShared[$argument]) ?
                    $this->create($argument) :
                    $this->get($argument);
                unset($this->_creationStack[$className]);
            }
            $resolvedArguments[] = $argument;
            $paramPosition++;
        }
        return $resolvedArguments;
    }

    /**
     * Resolve Class name
     *
     * @param string $className
     * @return string
     * @throws LogicException
     */
    protected function _resolveClassName($className)
    {
        $preferencePath = array();
        while (isset($this->_preferences[$className])) {
            if (isset($preferencePath[$this->_preferences[$className]])) {
                throw new LogicException(
                    'Circular type preference: ' . $className . ' relates to '
                        . $this->_preferences[$className] . ' and viceversa.'
                );
            }
            $className = $this->_preferences[$className];
            $preferencePath[$className] = 1;
        }
        return $className;
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $resolvedClassName
     * @param array $arguments
     * @return object
     * @throws LogicException
     * @throws BadMethodCallException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _create($resolvedClassName, array $arguments = array())
    {
        $parameters = $this->_definitions->getParameters($resolvedClassName);
        if ($parameters == null) {
            return new $resolvedClassName();
        }
        $args = $this->_resolveArguments($resolvedClassName, $parameters, $arguments);

        switch(count($args)) {
            case 1:
                return new $resolvedClassName($args[0]);

            case 2:
                return new $resolvedClassName($args[0], $args[1]);

            case 3:
                return new $resolvedClassName($args[0], $args[1], $args[2]);

            case 4:
                return new $resolvedClassName($args[0], $args[1], $args[2], $args[3]);

            case 5:
                return new $resolvedClassName($args[0], $args[1], $args[2], $args[3], $args[4]);

            case 6:
                return new $resolvedClassName($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);

            case 7:
                return new $resolvedClassName($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);

            case 8:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]
                );

            case 9:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]
                );

            case 10:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9]
                );

            case 11:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10]
                );

            case 12:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11]
                );

            case 13:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12]
                );

            case 14:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13]
                );

            case 15:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13], $args[14]
                );

            case 16:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13], $args[14], $args[15]
                );

            case 17:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13], $args[14], $args[15], $args[16]
                );

            case 18:
                return new $resolvedClassName(
                    $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9],
                    $args[10], $args[11], $args[12], $args[13], $args[14], $args[15], $args[16], $args[17]
                );

            default:
                $reflection = new \ReflectionClass($resolvedClassName);
                return $reflection->newInstanceArgs($args);
        }
    }

    /**
     * Create new object instance
     *
     * @param string $className
     * @param array $arguments
     * @return mixed
     */
    public function create($className, array $arguments = array())
    {
        if (isset($this->_preferences[$className])) {
            $className = $this->_resolveClassName($className);
        }
        return $this->_create($className, $arguments);
    }

    /**
     * Retrieve cached object instance
     *
     * @param string $className
     * @return mixed
     */
    public function get($className)
    {
        if (isset($this->_preferences[$className])) {
            $className = $this->_resolveClassName($className);
        }
        if (!isset($this->_sharedInstances[$className])) {
            $this->_sharedInstances[$className] = $this->_create($className);
        }
        return $this->_sharedInstances[$className];
    }

    /**
     * Configure di instance
     *
     * @param array $configuration
     */
    public function configure(array $configuration)
    {
        foreach ($configuration as $key => $curConfig) {
            switch ($key) {
                case 'preferences':
                    $this->_preferences = array_replace($this->_preferences, $curConfig);
                    break;

                default:
                    if (isset($curConfig['parameters'])) {
                        if (isset($this->_arguments[$key])) {
                            $this->_arguments[$key] = array_replace($this->_arguments[$key], $curConfig['parameters']);
                        } else {
                            $this->_arguments[$key] = $curConfig['parameters'];
                        }
                    }
                    if (isset($curConfig['shared'])) {
                        if (!$curConfig['shared'] || $curConfig['shared'] == 'false') {
                            $this->_nonShared[$key] = 1;
                        } else {
                            unset($this->_nonShared[$key]);
                        }
                    }
                    break;
            }
        }
    }
}
