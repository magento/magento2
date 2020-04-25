<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Js;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\Js\Cookie;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Validator\Ip;
use Magento\Framework\Session\Config;
use Magento\Framework\View\Element\Template\File\Validator;

class CookieTest extends TestCase
{
    /**
     * @var Cookie
     */
    protected $model;

    /**
     * @var MockObject|Context
     */
    protected $contextMock;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $sessionConfigMock;

    /**
     * @var Ip|MockObject
     */
    protected $ipValidatorMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ipValidatorMock = $this->getMockBuilder(Ip::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validtorMock = $this->getMockBuilder(Validator::class)
            ->setMethods(['isValid'])->disableOriginalConstructor()->getMock();

        $scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->setMethods(['isSetFlag'])->disableOriginalConstructor()->getMock();

        $this->contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfigMock));

        $this->contextMock->expects($this->any())
            ->method('getValidator')
            ->will($this->returnValue($validtorMock));

        $this->model = new Cookie(
            $this->contextMock,
            $this->sessionConfigMock,
            $this->ipValidatorMock,
            []
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(Cookie::class, $this->model);
    }

    /**
     * @dataProvider domainDataProvider
     */
    public function testGetDomain($domain, $isIp, $expectedResult)
    {
        $this->sessionConfigMock->expects($this->once())
            ->method('getCookieDomain')
            ->will($this->returnValue($domain));
        $this->ipValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($this->equalTo($domain))
            ->will($this->returnValue($isIp));

        $result = $this->model->getDomain($domain);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public static function domainDataProvider()
    {
        return [
            ['127.0.0.1', true, '127.0.0.1'],
            ['example.com', false, '.example.com'],
            ['.example.com', false, '.example.com'],
        ];
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
