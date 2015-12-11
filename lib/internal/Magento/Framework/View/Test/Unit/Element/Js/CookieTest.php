<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Js;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Js\Cookie
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Element\Template\Context
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionConfigMock;

    /**
     * @var \Magento\Framework\Session\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionConfigMock = $this->getMockBuilder('Magento\Framework\Session\Config\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Framework\Session\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\Framework\View\Element\Js\Cookie(
            $this->contextMock,
            $this->sessionConfigMock,
            $this->configMock,
            []
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Magento\Framework\View\Element\Js\Cookie', $this->model);
    }

    public function testGetPath()
    {
        $path = 'test_path';

        $this->sessionConfigMock->expects($this->once())
            ->method('getCookiePath')
            ->will($this->returnValue($path));

        $result = $this->model->getPath();
        $this->assertEquals($path, $result);
    }

    public function testGetLifetime()
    {
        $lifetime = 3600;
        $this->sessionConfigMock->expects(static::once())
            ->method('getCookieLifetime')
            ->willReturn($lifetime);

        $this->assertEquals($lifetime, $this->model->getLifetime());
    }
}
