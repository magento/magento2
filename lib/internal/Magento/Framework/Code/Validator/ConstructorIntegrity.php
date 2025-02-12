<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\ValidatorInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;

/**
 * Class constructor validator. Validates call of parent construct
 */
class ConstructorIntegrity implements ValidatorInterface
{
    /**
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_argumentsReader;

    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader
     */
    public function __construct(?\Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader = null)
    {
        $this->_argumentsReader = $argumentsReader ?: new \Magento\Framework\Code\Reader\ArgumentsReader();
    }

    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws ValidatorException
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
            $reIndexedCallArguments = array_column($callArguments, null, 'name');
            if (isset($reIndexedCallArguments[$requiredArgument['name']])) {
                if ($reIndexedCallArguments[$requiredArgument['name']]['isNamedArgument'] === true) {
                    $actualArgument = $reIndexedCallArguments[$requiredArgument['name']];
                    $this->checkCompatibleTypes($requiredArgument['type'], $actualArgument['type'], $class);
                    continue;
                }
            }

            if (isset($callArguments[$index]) && $callArguments[$index]['isNamedArgument'] === true) {
                $this->checkIfRequiredArgumentIsOptional($requiredArgument, $class);
            }

            if (isset($callArguments[$index])) {
                $actualArgument = $callArguments[$index];
                $this->checkCompatibleTypes($requiredArgument['type'], $actualArgument['type'], $class);
            } else {
                $this->checkIfRequiredArgumentIsOptional($requiredArgument, $class);
            }
        }

        return true;
    }

    /**
     * Check argument type compatibility
     *
     * @param string $requiredArgumentType
     * @param string $actualArgumentType
     * @param \ReflectionClass $class
     * @return void
     * @throws ValidatorException
     */
    private function checkCompatibleTypes(
        $requiredArgumentType,
        $actualArgumentType,
        \ReflectionClass $class
    ): void {
        $isCompatibleTypes = $this->_argumentsReader->isCompatibleType(
            $requiredArgumentType,
            $actualArgumentType
        );

        if (!$isCompatibleTypes) {
            $classPath = str_replace('\\', '/', $class->getFileName());
            throw new ValidatorException(
                new Phrase(
                    'Incompatible argument type: Required type: %1. Actual type: %2; File: %3%4%5',
                    [$requiredArgumentType, $actualArgumentType, PHP_EOL, $classPath, PHP_EOL]
                )
            );
        }
    }

    /**
     * Check if required argument is optional
     *
     * @param array $requiredArgument
     * @param \ReflectionClass $class
     * @return void
     * @throws ValidatorException
     */
    private function checkIfRequiredArgumentIsOptional(array $requiredArgument, \ReflectionClass $class): void
    {
        if (!$requiredArgument['isOptional']) {
            $classPath = str_replace('\\', '/', $class->getFileName());
            throw new ValidatorException(
                new Phrase(
                    'Missed required argument %1 in parent::__construct call. File: %2',
                    [$requiredArgument['name'], $classPath]
                )
            );
        }
    }
}
