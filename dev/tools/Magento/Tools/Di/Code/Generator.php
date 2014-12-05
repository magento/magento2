<?php
/**
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
