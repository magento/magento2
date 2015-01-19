<?php
/**
 * Class constructor validator
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Validator;

use Magento\Framework\Code\ValidationException;
use Magento\Framework\Code\ValidatorInterface;

class ContextAggregation implements ValidatorInterface
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
     * Validate class. Check declaration of dependencies that already declared in context object
     *
     * @param string $className
     * @return bool
     * @throws ValidationException
     */
    public function validate($className)
    {
        $class = new \ReflectionClass($className);
        $classArguments = $this->_argumentsReader->getConstructorArguments($class);

        $errors = [];
        $contextDependencies = [];

        $actualDependencies = $this->_getObjectArguments($classArguments);

        foreach ($actualDependencies as $type) {
            /** Check if argument is context object */
            if (is_subclass_of($type, '\Magento\Framework\ObjectManager\ContextInterface')) {
                $contextDependencies = array_merge(
                    $contextDependencies,
                    $this->_argumentsReader->getConstructorArguments(new \ReflectionClass($type), false, true)
                );
            }
        }

        $contextDependencyTypes = $this->_getObjectArguments($contextDependencies);

        foreach ($actualDependencies as $type) {
            if (in_array($type, $contextDependencyTypes)) {
                $errors[] = $type . ' already exists in context object';
            }
        }

        if (false == empty($errors)) {
            $classPath = str_replace('\\', '/', $class->getFileName());
            throw new ValidationException(
                'Incorrect dependency in class ' . $className . ' in ' . $classPath . PHP_EOL . implode(
                    PHP_EOL,
                    $errors
                )
            );
        }
        return true;
    }

    /**
     * Get arguments with object types
     *
     * @param array $arguments
     * @return array
     */
    protected function _getObjectArguments(array $arguments)
    {
        $output = [];
        foreach ($arguments as $argument) {
            $type = $argument['type'];
            if (!$type || $type == 'array') {
                continue;
            }
            $output[] = $type;
        }

        return $output;
    }
}
