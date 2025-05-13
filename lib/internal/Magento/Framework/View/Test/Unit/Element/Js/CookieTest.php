<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Js;

use Magento\Framework\Session\Config;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Validator\Ip;
use Magento\Framework\View\Element\Js\Cookie;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template\File\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
            ->onlyMethods(['isValid'])->disableOriginalConstructor()
            ->getMock();

        $scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->onlyMethods(['isSetFlag'])->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($scopeConfigMock);

        $this->contextMock->expects($this->any())
            ->method('getValidator')
            ->willReturn($validtorMock);

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
            ->willReturn($domain);
        $this->ipValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($domain)
            ->willReturn($isIp);

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
            ->willReturn($path);

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
