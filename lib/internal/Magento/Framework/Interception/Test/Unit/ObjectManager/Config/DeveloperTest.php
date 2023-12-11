<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\ObjectManager\Config;

use Magento\Framework\Interception\ConfigInterface;
use Magento\Framework\Interception\ObjectManager\Config\Developer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeveloperTest extends TestCase
{
    /**
     * @var Developer
     */
    private $model;

    /**
     * @var ConfigInterface|MockObject
     */
    private $interceptionConfig;

    protected function setUp(): void
    {
        $this->interceptionConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->model = new Developer();
    }

    public function testGetInstanceTypeReturnsInterceptorClass()
    {
        $this->interceptionConfig->expects($this->once())->method('hasPlugins')->willReturn(true);
        $this->model->setInterceptionConfig($this->interceptionConfig);

        $this->assertEquals('SomeClass\Interceptor', $this->model->getInstanceType('SomeClass'));
    }

    public function testGetInstanceTypeReturnsSimpleClassIfNoPluginsAreDeclared()
    {
        $this->model->setInterceptionConfig($this->interceptionConfig);

        $this->assertEquals('SomeClass', $this->model->getInstanceType('SomeClass'));
    }

    public function testGetInstanceTypeReturnsSimpleClassIfInterceptionConfigIsNotSet()
    {
        $this->assertEquals('SomeClass', $this->model->getInstanceType('SomeClass'));
    }

    public function testGetOriginalInstanceTypeReturnsInterceptedClass()
    {
        $this->interceptionConfig->expects($this->once())->method('hasPlugins')->willReturn(true);
        $this->model->setInterceptionConfig($this->interceptionConfig);

        $this->assertEquals('SomeClass\Interceptor', $this->model->getInstanceType('SomeClass'));
        $this->assertEquals('SomeClass', $this->model->getOriginalInstanceType('SomeClass'));
    }

    /**
     * Test correct instance type is returned when plugins are created for virtual type parents
     *
     * @return void
     */
    public function testGetInstanceTypeWithPluginOnVirtualTypeParent() : void
    {
        $reflectionClass = new \ReflectionClass(get_class($this->model));
        $reflectionProperty = $reflectionClass->getProperty('_virtualTypes');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, ['SomeVirtualClass' => 'SomeClass']);

        $this->interceptionConfig->expects($this->once())->method('hasPlugins')->with('SomeClass')->willReturn(true);
        $this->model->setInterceptionConfig($this->interceptionConfig);

        $instanceType = $this->model->getInstanceType('SomeVirtualClass');
        $this->assertEquals('SomeClass\Interceptor', $instanceType);
    }
}
