<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Test\Unit\ObjectManager\Config;

use \Magento\Framework\Interception\ObjectManager\Config\Developer;

class DeveloperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Interception\ObjectManager\Config\Developer
     */
    private $model;

    /**
     * @var \Magento\Framework\Interception\ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $interceptionConfig;

    protected function setUp()
    {
        $this->interceptionConfig = $this->getMock('\Magento\Framework\Interception\ConfigInterface');
        $this->model = new Developer();
    }

    public function testGetInstanceTypeReturnsInterceptorClass()
    {
        $this->interceptionConfig->expects($this->once())->method('hasPlugins')->will($this->returnValue(true));
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
        $this->interceptionConfig->expects($this->once())->method('hasPlugins')->will($this->returnValue(true));
        $this->model->setInterceptionConfig($this->interceptionConfig);

        $this->assertEquals('SomeClass\Interceptor', $this->model->getInstanceType('SomeClass'));
        $this->assertEquals('SomeClass', $this->model->getOriginalInstanceType('SomeClass'));
    }
}
