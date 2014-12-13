<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Interception\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Interception\ObjectManager\Config
     */
    private $model;

    /**
     * @var \Magento\Framework\Interception\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    private $interceptionConfig;

    /** @var  \Magento\Framework\ObjectManager\Config\Config | \PHPUnit_Framework_MockObject_MockObject */
    private $subjectConfigMock;

    protected function setUp()
    {
        $this->interceptionConfig = $this->getMock('\Magento\Framework\Interception\ConfigInterface');

        $this->subjectConfigMock = $this->getMockBuilder('\Magento\Framework\ObjectManager\Config\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getInstanceType'])
            ->getMock();

        $this->model = new Config($this->subjectConfigMock);
    }

    public function testGetInstanceTypeReturnsInterceptorClass()
    {
        $instanceName = 'SomeClass';

        $this->interceptionConfig->expects($this->once())
            ->method('hasPlugins')
            ->willReturn(true);

        $this->subjectConfigMock->expects($this->once())
            ->method('getInstanceType')
            ->with($instanceName)
            ->willReturn($instanceName);

        $this->model->setInterceptionConfig($this->interceptionConfig);

        $this->assertEquals('SomeClass\Interceptor', $this->model->getInstanceType($instanceName));
    }

    public function testGetInstanceTypeReturnsSimpleClassIfNoPluginsAreDeclared()
    {
        $instanceName = 'SomeClass';

        $this->subjectConfigMock->expects($this->once())
            ->method('getInstanceType')
            ->with($instanceName)
            ->willReturn($instanceName);

        $this->model->setInterceptionConfig($this->interceptionConfig);

        $this->assertEquals('SomeClass', $this->model->getInstanceType($instanceName));
    }

    public function testGetInstanceTypeReturnsSimpleClassIfInterceptionConfigIsNotSet()
    {
        $instanceName = 'SomeClass';

        $this->subjectConfigMock->expects($this->once())
            ->method('getInstanceType')
            ->with($instanceName)
            ->willReturn($instanceName);

        $this->assertEquals('SomeClass', $this->model->getInstanceType($instanceName));
    }

    public function testGetOriginalInstanceTypeReturnsInterceptedClass()
    {
        $this->interceptionConfig->expects($this->once())
            ->method('hasPlugins')
            ->willReturn(true);

        $instanceName = 'SomeClass';

        $this->subjectConfigMock->expects($this->exactly(2))
            ->method('getInstanceType')
            ->with($instanceName)
            ->willReturn($instanceName);

        $this->model->setInterceptionConfig($this->interceptionConfig);

        $this->assertEquals('SomeClass\Interceptor', $this->model->getInstanceType($instanceName));
        $this->assertEquals('SomeClass', $this->model->getOriginalInstanceType($instanceName));
    }
}
