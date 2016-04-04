<?php
/**
 * Class constructor validator. Validates arguments sequence
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\ValidatorInterface;

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
     * @throws \Magento\Framework\Exception\ValidatorException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate($className)
    {
        $class = new \ReflectionClass($className);
        $classArguments = $this->_argumentsReader->getConstructorArguments($class);

        if ($this->_isContextOnly($classArguments)) {
            return true;
        }

        $parent = $class->getParentClass();
        $parentArguments = [];
        if ($parent) {
            $parentClass = $parent->getName();
            if (0 !== strpos($parentClass, '\\')) {
                $parentClass = '\\' . $parentClass;
            }

            if (isset($this->_cache[$parentClass])) {
                $parentCall = $this->_argumentsReader->getParentCall($class, []);
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
            throw new \Magento\Framework\Exception\ValidatorException(
                new \Magento\Framework\Phrase(
                    'Incorrect argument sequence in class %1 in %2%3Required: $%4%5Actual  : $%6%7',
                    [
                        $className,
                        $classPath,
                        PHP_EOL,
                        implode(', $', array_keys($requiredSequence)),
                        PHP_EOL,
                        implode(', $', array_keys($classArguments)),
                        PHP_EOL
                    ]
                )
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
        $actualArgumentSequence = [];
        $requiredArgumentSequence = [];

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _buildsSequence(array $classArguments, array $parentArguments = [])
    {
        $output = [];
        if (empty($classArguments)) {
            return $parentArguments;
        }

        $classArgumentList = $this->_sortArguments($classArguments);
        $parentArgumentList = $this->_sortArguments($parentArguments);

        $migrated = [];
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
        $required = [];
        $optional = [];

        foreach ($arguments as $name => $argument) {
            if ($argument['isOptional']) {
                $optional[$name] = $argument;
            } else {
                $required[$name] = $argument;
            }
        }

        return [self::REQUIRED => $required, self::OPTIONAL => $optional];
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
