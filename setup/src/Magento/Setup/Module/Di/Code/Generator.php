<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code;

use Magento\Framework\Code\Generator as FrameworkGenerator;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Generator
 * @package Magento\Setup\Module\Di\Code
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
     * @param ObjectManagerInterface $objectManagerInterface
     * @param FrameworkGenerator\Io $ioObject
     * @param array $generatedEntities
     * @param DefinedClasses $definedClasses
     */
    public function __construct(
        ObjectManagerInterface $objectManagerInterface,
        ?\Magento\Framework\Code\Generator\Io $ioObject = null,
        array $generatedEntities = [],
        ?DefinedClasses $definedClasses = null
    ) {
        parent::__construct($ioObject, $generatedEntities, $definedClasses);
        $this->setObjectManager($objectManagerInterface);
    }

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
     * @throws \Magento\Framework\Exception\LocalizedException
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
