<?php
/**
 * Class constructor validator
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

        $errors = array();
        $contextDependencies = array();

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
        $output = array();
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
