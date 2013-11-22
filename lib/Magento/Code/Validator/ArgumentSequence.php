<?php
/**
 * Class constructor validator. Validates arguments sequence
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Code\Validator;
use Magento\Code\ValidatorInterface;
use Magento\Code\ValidationException;

class ArgumentSequence implements ValidatorInterface
{
    const TYPE_OBJECT = 'object';
    const TYPE_SCALAR = 'scalar';

    const REQUIRED = 'required';
    const OPTIONAL = 'optional';

    /**
     * @var \Magento\Code\Reader\ArgumentsReader
     */
    protected $_argumentsReader;

    /**
     * List of allowed type to validate
     * @var array
     */
    protected $_allowedTypes = array('\Magento\App\Action\Action', '\Magento\View\Element\BlockInterface');

    /**
     * @var array
     */
    protected $_cache;

    /**
     * @param \Magento\Code\Reader\ArgumentsReader $argumentsReader
     */
    public function __construct(\Magento\Code\Reader\ArgumentsReader $argumentsReader = null)
    {
        $this->_argumentsReader = $argumentsReader ?: new \Magento\Code\Reader\ArgumentsReader();
    }

    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws ValidationException
     */
    public function validate($className)
    {
        /** Temporary solution. Need to be removed since all AC of MAGETWO-14343 will be covered */
        if (!$this->_isAllowedType($className)) {
            return true;
        }

        $class = new \ReflectionClass($className);
        $classArguments = $this->_argumentsReader->getConstructorArguments($class);

        if ($this->_isContextOnly($classArguments)) {
            return true;
        }

        $parent = $class->getParentClass();
        $parentArguments = array();
        if ($parent) {
            $parentClass = $parent->getName();
            if (0 !== strpos($parentClass, '\\')) {
                $parentClass = '\\' . $parentClass;
            }

            if (isset($this->_cache[$parentClass])) {
                $parentArguments = $this->_cache[$parentClass];
            } else {
                $parentArguments = $this->_argumentsReader->getConstructorArguments($parent, false, true);
            }
        }

        $requiredSequence = $this->_buildsSequence($classArguments, $parentArguments);
        $this->_cache[$className] = $requiredSequence;

        if (false == $this->_checkArgumentSequence($classArguments, $requiredSequence)) {
            throw new ValidationException(
                'Incorrect argument sequence in class ' . $className . ' in ' . $class->getFileName() . PHP_EOL
                . 'Required: $' . implode(', $', array_keys($requiredSequence)) . PHP_EOL
                . 'Actual  : $' . implode(', $', array_keys($classArguments)) . PHP_EOL
            );
        }

        return true;
    }

    /**
     * Check whether type can be validated
     *
     * @param string $className
     * @return bool
     */
    protected function _isAllowedType($className)
    {
        foreach ($this->_allowedTypes as $type) {
            if (is_subclass_of($className, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check argument sequence
     *
     * @param array $actualSequence
     * @param array $requiredSequence
     * @return bool
     */
    protected function _checkArgumentSequence(array $actualSequence, array $requiredSequence)
    {
        $actual = array_keys($actualSequence);
        $required = array_keys($requiredSequence);
        return $actual === $required;
    }

    /**
     * Build argument required sequence
     *
     * @param array $classArguments
     * @param array $parentArguments
     * @return array
     */
    protected function _buildsSequence(array $classArguments, array $parentArguments = array())
    {
        if (empty($classArguments)) {
            return $classArguments;
        }

        $classArgumentList = $this->_sortArguments($classArguments);
        $parentArgumentList = $this->_sortArguments($parentArguments);

        $requiredToOptionalObject = array();
        $requiredToOptionalScalar = array();
        $output = array();

        /**
         * Argument Sequence Matrix
         *      1        2       3
         * 1. P.R.O    C.O.O   C.R.O
         * 2. P.R.S    C.O.S   C.R.S
         * 3. P.O.O    C.R.O   C.O.O
         * 4. P.O.S    C.R.S   C.O.S
         *
         * where code X.Y.Z
         * X - parent (P)   / child (C)
         * Y - required (R) / optional (O)
         * Z - object (O)   / scalar (S)
         */

        // 1. Parent Required Object Arguments
        foreach ($parentArgumentList[self::REQUIRED][self::TYPE_OBJECT] as $name => $argument) {
            if (isset($classArgumentList[self::OPTIONAL][self::TYPE_OBJECT][$name])) {
                // 1.2
                $requiredToOptionalObject[$name] = $classArgumentList[self::OPTIONAL][self::TYPE_OBJECT][$name];
            } elseif (isset($classArgumentList[self::REQUIRED][self::TYPE_OBJECT][$name])) {
                // 1.3
                $output[$name] = $classArgumentList[self::REQUIRED][self::TYPE_OBJECT][$name];
            } else {
                // 1.1
                $output[$name] = $argument;
            }
        }

        // 2. Parent Required Scalar Arguments
        foreach ($parentArgumentList[self::REQUIRED][self::TYPE_SCALAR] as $name => $argument) {
            if (isset($classArgumentList[self::OPTIONAL][self::TYPE_SCALAR][$name])) {
                // 2.2
                $requiredToOptionalScalar[$name] = $classArgumentList[self::OPTIONAL][self::TYPE_SCALAR][$name];
            } elseif (isset($classArgumentList[self::REQUIRED][self::TYPE_SCALAR][$name])) {
                // 2.3
                $output[$name] = $classArgumentList[self::REQUIRED][self::TYPE_SCALAR][$name];
            } else {
                // 2.1
                $output[$name] = $argument;
            }
        }

        // 1.3 Child Required Object Arguments
        foreach ($classArgumentList[self::REQUIRED][self::TYPE_OBJECT] as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = $argument;
            }
        }

        // 2.3 Child Required Scalar Arguments
        foreach ($classArgumentList[self::REQUIRED][self::TYPE_SCALAR] as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = $argument;
            }
        }

        // 1.2 Optional Object. Parent Required Object Arguments that become Optional in Child Class
        foreach ($requiredToOptionalObject as $name => $argument) {
            $output[$name] = $argument;
        }

        // 2.2 Optional Scalar. Parent Required Scalar Arguments that become Optional in Child Class
        foreach ($requiredToOptionalScalar as $name => $argument) {
            $output[$name] = $argument;
        }

        // 3. Parent Optional Object Arguments
        foreach ($parentArgumentList[self::OPTIONAL][self::TYPE_OBJECT] as $name => $argument) {
            if (isset($classArgumentList[self::OPTIONAL][self::TYPE_OBJECT][$name])) {
                // 3.3 Use Child Optional Object
                $output[$name] = $classArgumentList[self::OPTIONAL][self::TYPE_OBJECT][$name];
            } elseif (!isset($output[$name])) {
                // 3.2 Check whether this argument wasn't processed in Step 1.2 or 1.3
                $output[$name] = $argument;
            } else {
                // 3.1 Use Parent Optional Object Argument
                $output[$name] = $argument;
            }
        }

        // 4. Parent Optional Scalar Arguments
        foreach ($parentArgumentList[self::OPTIONAL][self::TYPE_SCALAR] as $name => $argument) {
            if (isset($classArgumentList[self::OPTIONAL][self::TYPE_SCALAR][$name])) {
                // 4.3 Use Child Optional Scalar
                $output[$name] = $classArgumentList[self::OPTIONAL][self::TYPE_SCALAR][$name];
            } elseif (!isset($output[$name])) {
                // 4.2 Check whether this argument wasn't processed in Step 2.2 or 2.3
                $output[$name] = $argument;
            } else {
                // 4.1 Use Parent Optional Scalar Argument
                $output[$name] = $argument;
            }
        }

        // 3.3 Child Optional Object Arguments
        foreach ($classArgumentList[self::OPTIONAL][self::TYPE_OBJECT] as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = $argument;
            }
        }

        // 4.3 Child Optional Scalar Arguments
        foreach ($classArgumentList[self::OPTIONAL][self::TYPE_SCALAR] as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = $argument;
            }
        }

        return $output;
    }

    /**
     * Sort arguments
     *
     * @param array $arguments
     * @return array
     */
    protected function _sortArguments($arguments)
    {
        $requiredObject = array();
        $requiredScalar = array();
        $optionalObject = array();
        $optionalScalar = array();

        foreach ($arguments as $argument) {
            if ($argument['type'] && $argument['type'] != 'array') {
                if ($argument['isOptional']) {
                    $optionalObject[$argument['name']] = $argument;
                } else {
                    $requiredObject[$argument['name']] = $argument;
                }
            } else {
                if ($argument['isOptional']) {
                    $optionalScalar[$argument['name']] = $argument;
                } else {
                    $requiredScalar[$argument['name']] = $argument;
                }
            }
        }

        $requiredObject = $this->_sortObjectType($requiredObject);
        $optionalScalar = $this->_sortScalarType($optionalScalar);

        return array(
            self::REQUIRED => array(
                self::TYPE_OBJECT => $requiredObject,
                self::TYPE_SCALAR => $requiredScalar
            ),
            self::OPTIONAL => array(
                self::TYPE_OBJECT => $optionalObject,
                self::TYPE_SCALAR => $optionalScalar
            ),
        );
    }

    /**
     * Sort arguments by context object
     *
     * @param array $argumentList
     * @return array
     */
    protected function _sortObjectType(array $argumentList)
    {
        $context = array();
        foreach ($argumentList as $name => $argument) {
            if ($this->_isContextType($argument['type'])) {
                $context[$name] = $argument;
                unset($argumentList[$name]);
                break;
            }
        }
        return array_merge($context, $argumentList);
    }

    /**
     * Sort arguments by arguments name
     *
     * @param array $argumentList
     * @return array
     */
    protected function _sortScalarType(array $argumentList)
    {
        $data = array();
        foreach ($argumentList as $name => $argument) {
            if ($argument['name'] == 'data') {
                $data[$name] = $argument;
                unset($argumentList[$name]);
                break;
            }
        }
        return array_merge($data, $argumentList);
    }

    /**
     * Check whether arguments list contains an only context argument
     *
     * @param array $arguments
     * @return bool
     */
    protected function _isContextOnly(array $arguments)
    {
        if (count($arguments) !== 1) {
            return false;
        }
        $argument = current($arguments);
        return $argument['type'] && $this->_isContextType($argument['type']);
    }

    /**
     * Check whether type is context object
     *
     * @param string $type
     * @return bool
     */
    protected function _isContextType($type)
    {
        return is_subclass_of($type, '\Magento\ObjectManager\ContextInterface');
    }
}
