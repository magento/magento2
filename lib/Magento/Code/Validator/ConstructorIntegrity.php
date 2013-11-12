<?php
/**
 * Class constructor validator. Validates call of parent construct
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

class ConstructorIntegrity
{
    /**
     * Validate class
     *
     * @param $className
     * @return bool
     * @throws \Magento\Code\ValidationException
     */
    public function validate($className)
    {
        $class = new \ReflectionClass($className);
        $parent = $class->getParentClass();

        /** Check whether parent class exists and has __construct method */
        if (!$parent || !$parent->hasMethod('__construct')) {
            return true;
        }

        /** Check whether class has __construct */
        $classArguments = $this->_getConstructorArguments($class);
        if (null === $classArguments) {
            return true;
        }

        /** Check whether class has parent::__construct call */
        $callArguments = $this->_getParentCall($class, $classArguments);
        if (null === $callArguments) {
            return true;
        }

        /** Get parent class __construct arguments */
        $parentArguments = $this->_getConstructorArguments($parent, true);

        foreach ($parentArguments as $index => $requiredArgument) {
            if (isset($callArguments[$index])) {
                $actualArgument = $callArguments[$index];
            } else {
                if ($requiredArgument['isOptional']) {
                    continue;
                }

                throw new \Magento\Code\ValidationException('Missed required argument ' . $requiredArgument['name']
                    . ' in parent::__construct call. File: ' . $class->getFileName()
                );
            }

            if (false == $this->_isCompatibleType($requiredArgument['type'], $actualArgument['type'])) {
                throw new \Magento\Code\ValidationException('Incompatible argument type: Required type: '
                    . $requiredArgument['type'] . '. Actual type: ' . $actualArgument['type']
                    . '; File: ' . $class->getFileName()
                );
            }
        }

        /**
         * Try to detect unused arguments
         * Check whether count of passed parameters less or equal that count of count parent class arguments
         */
        if (count($callArguments) > count($parentArguments)) {
            $extraParameters = array_slice($callArguments, count($parentArguments));
            $names = array();
            foreach ($extraParameters as $param) {
                $names[] = '$' . $param['name'];
            }

            throw new \Magento\Code\ValidationException(
                'Extra parameters passed to parent construct: '
                . implode(', ', $names)
                . '. File: ' . $class->getFileName()
            );
        }
        return true;
    }

    /**
     * Check argument type compatibility
     *
     * @param string $requiredType
     * @param string $actualType
     * @return bool
     */
    protected function _isCompatibleType($requiredType, $actualType)
    {
        /** Types are compatible if type names are equal */
        if ($requiredType === $actualType) {
            return true;
        }

        /** Types are 'semi-compatible' if one of them are undefined */
        if ($requiredType === null || $actualType === null) {
            return true;
        }

        /**
         * Special case for scalar arguments
         * Array type is compatible with array or null type. Both of these types are checked above
         */
        if ($requiredType === 'array' || $actualType === 'array') {
            return false;
        }

        return is_subclass_of($actualType, $requiredType);
    }

    /**
     * Get arguments of parent __construct call
     *
     * @param \ReflectionClass $class
     * @param array $classArguments
     * @return array|null
     */
    protected function _getParentCall(\ReflectionClass $class, array $classArguments)
    {
        $trimFunction = function (&$value) {
            $value = trim($value, PHP_EOL . ' $');
        };

        $method = $class->getMethod('__construct');
        $start = $method->getStartLine();
        $end = $method->getEndLine();
        $length = $end - $start;

        $source = file($class->getFileName());
        $content = implode('', array_slice($source, $start, $length));
        $pattern = '/parent::__construct\(([a-zA-Z0-9_$, \n]*)\);/';

        if (!preg_match($pattern, $content, $matches)) {
            return null;
        }

        $arguments = $matches[1];
        if (!trim($arguments)) {
            return null;
        }

        $arguments = explode(',', $arguments);
        array_walk($arguments, $trimFunction);

        $output = array();
        foreach ($arguments as $argumentPosition => $argumentName) {
            $type = isset($classArguments[$argumentName]) ? $classArguments[$argumentName]['type'] : null;
            $output[$argumentPosition] = array(
                'name' => $argumentName,
                'position' => $argumentPosition,
                'type' => $type,
            );
        }
        return $output;
    }

    /**
     * Get class constructor
     *
     * @param \ReflectionClass $class
     * @param bool $groupByPosition
     * @return array|null
     */
    protected function _getConstructorArguments(\ReflectionClass $class, $groupByPosition = false)
    {
        if (false == $class->hasMethod('__construct')) {
            return null;
        }

        $output = array();
        foreach ($class->getConstructor()->getParameters() as $parameter) {
            $name = $parameter->getName();
            $position = $parameter->getPosition();
            $parameterClass = $parameter->getClass();
            $type = $parameterClass ? $parameterClass->getName() : ($parameter->isArray() ? 'array' : null);
            $index = $groupByPosition ? $position : $name;
            $output[$index] = array(
                'name' => $name,
                'position' => $position,
                'type' => $type,
                'isOptional' => $parameter->isOptional()
            );
        }
        return $output;
    }
} 
