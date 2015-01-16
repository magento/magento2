<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\Di\Code;

use Magento\Framework\Code\Generator as FrameworkGenerator;

/**
 * Class Generator
 * @package Magento\Tools\Di\Code
 */
class Generator extends FrameworkGenerator
{
    /**
     * List of class methods
     *
     * @var array
     */
    private $classMethods = [];

    /**
     * Create entity generator
     *
     * @param string $generatorClass
     * @param string $entityName
     * @param string $className
     * @return \Magento\Framework\Code\Generator\EntityAbstract
     */
    protected function createGeneratorInstance($generatorClass, $entityName, $className)
    {
        $generatorClass = parent::createGeneratorInstance($generatorClass, $entityName, $className);
        $generatorClass->setInterceptedMethods($this->classMethods);
        return $generatorClass;
    }

    /**
     * Generates list of classes
     *
     * @param array $classesToGenerate
     * @throws \Magento\Framework\Exception
     * @return void
     */
    public function generateList($classesToGenerate)
    {
        foreach ($classesToGenerate as $class => $methods) {
            $this->setClassMethods($methods);
            $this->generateClass($class . '\\Interceptor');
            $this->clearClassMethods();
        }
    }

    /**
     * Sets class methods
     *
     * @param array $methods
     * @return void
     */
    private function setClassMethods($methods)
    {
        $this->classMethods = $methods;
    }

    /**
     * Clear class methods
     * @return void
     */
    private function clearClassMethods()
    {
        $this->classMethods = [];
    }
}
