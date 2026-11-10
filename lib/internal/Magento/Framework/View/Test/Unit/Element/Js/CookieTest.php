<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use PHPUnit\Framework\Attributes\DataProvider;

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

    /**     */
    #[DataProvider('domainDataProvider')]
    public function testGetDomain($domain, $expectedResult)
    {
        $this->sessionConfigMock->expects($this->once())
            ->method('getCookieDomain')
            ->willReturn($domain);

        $result = $this->model->getDomain();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public static function domainDataProvider()
    {
        return [
            ['127.0.0.1', '127.0.0.1'],
            ['example.com', 'example.com'],
            ['.example.com', '.example.com'],
            ['sub.example.com', 'sub.example.com'],
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
