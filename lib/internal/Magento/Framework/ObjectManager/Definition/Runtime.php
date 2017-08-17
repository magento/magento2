<?php
/**
 * Runtime class definitions. \Reflection is used to parse constructor signatures. Should be used only in dev mode.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Definition;

/**
 * Class \Magento\Framework\ObjectManager\Definition\Runtime
 *
 */
class Runtime implements \Magento\Framework\ObjectManager\DefinitionInterface
{
    /**
     * @var array
     */
    protected $_definitions = [];

    /**
     * @param \Magento\Framework\Code\Reader\ClassReaderInterface $reader
     */
    public function __construct(\Magento\Framework\Code\Reader\ClassReaderInterface $reader = null)
    {
        $this->_reader = $reader ?: new \Magento\Framework\Code\Reader\ClassReader();
    }

    /**
     * Get list of method parameters
     *
     * Retrieve an ordered list of constructor parameters.
     * Each value is an array with following entries:
     *
     * array(
     *     0, // string: Parameter name
     *     1, // string|null: Parameter type
     *     2, // bool: whether this param is required
     *     3, // mixed: default value
     * );
     *
     * @param string $className
     * @return array|null
     */
    public function getParameters($className)
    {
        if (!array_key_exists($className, $this->_definitions)) {
            $this->_definitions[$className] = $this->_reader->getConstructor($className);
        }
        return $this->_definitions[$className];
    }

    /**
     * Retrieve list of all classes covered with definitions
     *
     * @return array
     */
    public function getClasses()
    {
        return [];
    }
}
