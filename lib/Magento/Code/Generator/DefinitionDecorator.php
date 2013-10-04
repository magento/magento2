<?php
/**
 * Object manager definition decorator. Generates all proxies and factories declared
 * in class constructor signatures before reading it's definition
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
namespace Magento\Code\Generator;

class DefinitionDecorator implements \Magento\ObjectManager\Definition
{
    /**
     * Processed classes list
     *
     * @var array
     */
    protected $_processedClasses = array();

    /**
     * Class generator
     *
     * @var \Magento\Code\Generator\ClassGenerator
     */
    protected $_generator;

    /**
     * Decorated objectManager definition
     *
     * @var \Magento\ObjectManager\Definition
     */
    protected $_decoratedDefinition;

    /**
     * @param \Magento\ObjectManager\Definition $definition
     * @param \Magento\Code\Generator\ClassGenerator $generator
     */
    public function __construct(
        \Magento\ObjectManager\Definition $definition, \Magento\Code\Generator\ClassGenerator $generator = null
    ) {
        $this->_decoratedDefinition = $definition;
        $this->_generator = $generator ?: new \Magento\Code\Generator\ClassGenerator();
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
        if (!array_key_exists($className, $this->_processedClasses)) {
            $this->_generator->generateForConstructor($className);
            $this->_processedClasses[$className] = 1;
        }
        return $this->_decoratedDefinition->getParameters($className);
    }

    /**
     * Retrieve list of all classes covered with definitions
     *
     * @return array
     */
    public function getClasses()
    {
        return $this->_decoratedDefinition->getClasses();
    }
}
