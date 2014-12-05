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
