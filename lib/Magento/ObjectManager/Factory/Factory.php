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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ObjectManager\Factory;

class Factory implements \Magento\ObjectManager\Factory
{
    /**
     * @var \Magento\ObjectManager\Config
     */
    protected $_config;

    /**
     * Definition list
     *
     * @var \Magento\ObjectManager\Definition
     */
    protected $_definitions;

    /**
     * @var array
     */
    private $_creationStack = array();

    /**
     * @var \Magento\Data\Argument\InterpreterInterface
     */
    protected $_argInterpreter;

    /**
     * @var \Magento\ObjectManager\Config\Argument\ObjectFactory
     */
    protected $_argObjectFactory;

    /**
     * @param \Magento\ObjectManager\Config $config
     * @param \Magento\Data\Argument\InterpreterInterface $argInterpreter
     * @param \Magento\ObjectManager\Config\Argument\ObjectFactory $argObjectFactory
     * @param \Magento\ObjectManager\Definition $definitions
     */
    public function __construct(
        \Magento\ObjectManager\Config $config,
        \Magento\Data\Argument\InterpreterInterface $argInterpreter,
        \Magento\ObjectManager\Config\Argument\ObjectFactory $argObjectFactory,
        \Magento\ObjectManager\Definition $definitions = null
    ) {
        $this->_config = $config;
        $this->_argInterpreter = $argInterpreter;
        $this->_argObjectFactory = $argObjectFactory;
        $this->_definitions = $definitions ?: new \Magento\ObjectManager\Definition\Runtime();
    }

    /**
     * Resolve constructor arguments
     *
     * @param string $requestedType
     * @param array $parameters
     * @param array $argumentValues
     * @return array
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _resolveArguments($requestedType, array $parameters, array $argumentValues = array())
    {
        $result = array();
        $arguments = $this->_config->getArguments($requestedType);
        foreach ($parameters as $parameter) {
            list($paramName, $paramType, $paramRequired, $paramDefault) = $parameter;
            if (array_key_exists($paramName, $argumentValues)) {
                $value = $argumentValues[$paramName];
            } else if (array_key_exists($paramName, $arguments)) {
                $argumentData = $arguments[$paramName];
                if (!is_array($argumentData)) {
                    throw new \UnexpectedValueException(
                        sprintf(
                            'Invalid parameter configuration provided for $%s argument of %s.',
                            $paramName,
                            $requestedType
                        )
                    );
                }
                try {
                    $value = $this->_argInterpreter->evaluate($argumentData);
                } catch (\Magento\Data\Argument\MissingOptionalValueException $e) {
                    $value = $paramDefault;
                }
            } else if ($paramRequired) {
                if (!$paramType) {
                    throw new \BadMethodCallException(
                        sprintf('Missing required argument $%s of %s.', $paramName, $requestedType)
                    );
                }
                $value = $this->_argObjectFactory->create($paramType);
            } else {
                $value = $paramDefault;
            }
            $result[] = $value;
        }
        return $result;
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function create($requestedType, array $arguments = array())
    {
        $type = $this->_config->getInstanceType($requestedType);
        $parameters = $this->_definitions->getParameters($type);
        if ($parameters == null) {
            return new $type();
        }
        $this->_assertNoCircularDependency($requestedType);
        $this->_creationStack[$requestedType] = $requestedType;
        try {
            $args = $this->_resolveArguments($requestedType, $parameters, $arguments);
            unset($this->_creationStack[$requestedType]);
        } catch (\Exception $e) {
            unset($this->_creationStack[$requestedType]);
            throw $e;
        }
        switch (count($args)) {
            case 1:
                return new $type($args[0]);
            case 2:
                return new $type($args[0], $args[1]);
            case 3:
                return new $type($args[0], $args[1], $args[2]);
            case 4:
                return new $type($args[0], $args[1], $args[2], $args[3]);
            case 5:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4]);
            case 6:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
            case 7:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
            case 8:
                return new $type($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
            default:
                $reflection = new \ReflectionClass($type);
                return $reflection->newInstanceArgs($args);
        }
    }

    /**
     * Prevent circular dependencies using creation stack
     *
     * @param string $type
     * @throws \LogicException
     * @return void
     */
    private function _assertNoCircularDependency($type)
    {
        if (isset($this->_creationStack[$type])) {
            $lastFound = end($this->_creationStack);
            $this->_creationStack = array();
            throw new \LogicException("Circular dependency: {$type} depends on {$lastFound} and vice versa.");
        }
    }
}
