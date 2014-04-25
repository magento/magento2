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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\ValidatorInterface;
use Magento\Framework\Code\ValidationException;

class ArgumentSequence implements ValidatorInterface
{
    const REQUIRED = 'required';

    const OPTIONAL = 'optional';

    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_argumentsReader;

    /**
     * @var array
     */
    protected $_cache;

    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader
     */
    public function __construct(\Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader = null)
    {
        $this->_argumentsReader = $argumentsReader ?: new \Magento\Framework\Code\Reader\ArgumentsReader();
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
                $parentCall = $this->_argumentsReader->getParentCall($class, array());
                if (empty($classArguments) || $parentCall) {
                    $parentArguments = $this->_cache[$parentClass];
                }
            }
        }

        if (empty($classArguments)) {
            $classArguments = $parentArguments;
        }

        $requiredSequence = $this->_buildsSequence($classArguments, $parentArguments);
        if (!empty($requiredSequence)) {
            $this->_cache[$className] = $requiredSequence;
        }

        if (false == $this->_checkArgumentSequence($classArguments, $requiredSequence)) {
            $classPath = str_replace('\\', '/', $class->getFileName());
            throw new ValidationException(
                'Incorrect argument sequence in class ' .
                $className .
                ' in ' .
                $classPath .
                PHP_EOL .
                'Required: $' .
                implode(
                    ', $',
                    array_keys($requiredSequence)
                ) . PHP_EOL . 'Actual  : $' . implode(
                    ', $',
                    array_keys($classArguments)
                ) . PHP_EOL
            );
        }

        return true;
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
        $actualArgumentSequence = array();
        $requiredArgumentSequence = array();

        foreach ($actualSequence as $name => $argument) {
            if (false == $argument['isOptional']) {
                $actualArgumentSequence[$name] = $argument;
            } else {
                break;
            }
        }

        foreach ($requiredSequence as $name => $argument) {
            if (false == $argument['isOptional']) {
                $requiredArgumentSequence[$name] = $argument;
            } else {
                break;
            }
        }
        $actual = array_keys($actualArgumentSequence);
        $required = array_keys($requiredArgumentSequence);
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
        $output = array();
        if (empty($classArguments)) {
            return $parentArguments;
        }

        $classArgumentList = $this->_sortArguments($classArguments);
        $parentArgumentList = $this->_sortArguments($parentArguments);

        $migrated = array();
        foreach ($parentArgumentList[self::REQUIRED] as $name => $argument) {
            if (!isset($classArgumentList[self::OPTIONAL][$name])) {
                $output[$name] = isset(
                    $classArgumentList[self::REQUIRED][$name]
                ) ? $classArgumentList[self::REQUIRED][$name] : $argument;
            } else {
                $migrated[$name] = $classArgumentList[self::OPTIONAL][$name];
            }
        }

        foreach ($classArgumentList[self::REQUIRED] as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = $argument;
            }
        }

        /** Use parent required argument that become optional in child class */
        foreach ($migrated as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = $argument;
            }
        }

        foreach ($parentArgumentList[self::OPTIONAL] as $name => $argument) {
            if (!isset($output[$name])) {
                $output[$name] = isset(
                    $classArgumentList[self::OPTIONAL][$name]
                ) ? $classArgumentList[self::OPTIONAL][$name] : $argument;
            }
        }

        foreach ($classArgumentList[self::OPTIONAL] as $name => $argument) {
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
        $required = array();
        $optional = array();

        foreach ($arguments as $name => $argument) {
            if ($argument['isOptional']) {
                $optional[$name] = $argument;
            } else {
                $required[$name] = $argument;
            }
        }

        return array(self::REQUIRED => $required, self::OPTIONAL => $optional);
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
        return is_subclass_of($type, '\Magento\Framework\ObjectManager\ContextInterface');
    }
}
