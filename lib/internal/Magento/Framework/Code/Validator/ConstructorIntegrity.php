<?php
/**
 * Class constructor validator. Validates call of parent construct
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\ValidatorInterface;
use Magento\Framework\Phrase;

class ConstructorIntegrity implements ValidatorInterface
{
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_argumentsReader;

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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate($className)
    {
        $class = new \ReflectionClass($className);
        $parent = $class->getParentClass();

        /** Check whether parent class exists and has __construct method */
        if (!$parent) {
            return true;
        }

        /** Get parent class __construct arguments */
        $parentArguments = $this->_argumentsReader->getConstructorArguments($parent, true, true);
        if (empty($parentArguments)) {
            return true;
        }

        /** Check whether class has __construct */
        $classArguments = $this->_argumentsReader->getConstructorArguments($class);
        if (null === $classArguments) {
            return true;
        }

        /** Check whether class has parent::__construct call */
        $callArguments = $this->_argumentsReader->getParentCall($class, $classArguments);
        if (null === $callArguments) {
            return true;
        }

        /** Get parent class __construct arguments */
        $parentArguments = $this->_argumentsReader->getConstructorArguments($parent, true, true);

        foreach ($parentArguments as $index => $requiredArgument) {
            if (isset($callArguments[$index])) {
                $actualArgument = $callArguments[$index];
            } else {
                if ($requiredArgument['isOptional']) {
                    continue;
                }

                $classPath = str_replace('\\', '/', $class->getFileName());
                throw new \Magento\Framework\Exception\ValidatorException(
                    new Phrase(
                        'Missed required argument %1 in parent::__construct call. File: %2',
                        [$requiredArgument['name'], $classPath]
                    )
                );
            }

            $isCompatibleTypes = $this->_argumentsReader->isCompatibleType(
                $requiredArgument['type'],
                $actualArgument['type']
            );
            if (false == $isCompatibleTypes) {
                $classPath = str_replace('\\', '/', $class->getFileName());
                throw new \Magento\Framework\Exception\ValidatorException(
                    new Phrase(
                        'Incompatible argument type: Required type: %1. Actual type: %2; File: %3%4%5',
                        [$requiredArgument['type'], $actualArgument['type'], PHP_EOL, $classPath, PHP_EOL]
                    )
                );
            }
        }

        /**
         * Try to detect unused arguments
         * Check whether count of passed parameters less or equal that count of count parent class arguments
         */
        if (count($callArguments) > count($parentArguments)) {
            $extraParameters = array_slice($callArguments, count($parentArguments));
            $names = [];
            foreach ($extraParameters as $param) {
                $names[] = '$' . $param['name'];
            }

            $classPath = str_replace('\\', '/', $class->getFileName());
            throw new \Magento\Framework\Exception\ValidatorException(
                new Phrase(
                    'Extra parameters passed to parent construct: %1. File: %2',
                    [implode(', ', $names), $classPath]
                )
            );
        }
        return true;
    }
}
