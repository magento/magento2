<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\AdminPathConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdminPathConfigTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $coreConfig;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $backendConfig;

    /**
     * @var UrlInterface|MockObject
     */
    protected $url;

    /**
     * @var AdminPathConfig
     */
    protected $adminPathConfig;

    protected function setUp(): void
    {
        $this->coreConfig = $this->getMockForAbstractClass(
            ScopeConfigInterface::class,
            [],
            '',
            false
        );
        $this->backendConfig = $this->getMockForAbstractClass(
            ConfigInterface::class,
            [],
            '',
            false
        );
        $this->url = $this->getMockForAbstractClass(
            UrlInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getBaseUrl']
        );
        $this->adminPathConfig = new AdminPathConfig($this->coreConfig, $this->backendConfig, $this->url);
    }

    public function testGetCurrentSecureUrl()
    {
        $request = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getPathInfo']
        );
        $request->expects($this->once())->method('getPathInfo')->willReturn('/info');
        $this->url->expects($this->once())->method('getBaseUrl')->with('link', true)->willReturn('localhost/');
        $this->assertEquals('localhost/info', $this->adminPathConfig->getCurrentSecureUrl($request));
    }

    /**
     * @param $unsecureBaseUrl
     * @param $useSecureInAdmin
     * @param $secureBaseUrl
     * @param $useCustomUrl
     * @param $customUrl
     * @param $expected
     * @dataProvider shouldBeSecureDataProvider
     */
    public function testShouldBeSecure(
        $unsecureBaseUrl,
        $useSecureInAdmin,
        $secureBaseUrl,
        $useCustomUrl,
        $customUrl,
        $expected
    ) {
        $coreConfigValueMap = $this->returnValueMap([
            [Store::XML_PATH_UNSECURE_BASE_URL, 'default', null, $unsecureBaseUrl],
            [Store::XML_PATH_SECURE_BASE_URL, 'default', null, $secureBaseUrl],
            ['admin/url/custom', 'default', null, $customUrl],
        ]);
        $backendConfigFlagsMap = $this->returnValueMap([
            [Store::XML_PATH_SECURE_IN_ADMINHTML, $useSecureInAdmin],
            ['admin/url/use_custom', $useCustomUrl],
        ]);
        $this->coreConfig->expects($this->atLeast(1))->method('getValue')
            ->will($coreConfigValueMap);
        $this->coreConfig->expects($this->atMost(2))->method('getValue')
            ->will($coreConfigValueMap);

        $this->backendConfig->expects($this->atMost(2))->method('isSetFlag')
            ->will($backendConfigFlagsMap);
        $this->assertEquals($expected, $this->adminPathConfig->shouldBeSecure(''));
    }

    /**
     * @return array
     */
    public static function shouldBeSecureDataProvider()
    {
        return [
            ['http://localhost/', false, 'default', false, '', false],
            ['http://localhost/', true, 'default', false, '', false],
            ['https://localhost/', false, 'default', false, '', true],
            ['https://localhost/', true, 'default', false, '', true],
            ['http://localhost/', false, 'https://localhost/', false, '', false],
            ['http://localhost/', true, 'https://localhost/', false, '', true],
            ['https://localhost/', true, 'https://localhost/', false, '', true],
        ];
    }

    public function testGetDefaultPath()
    {
        $this->backendConfig->expects($this->once())
            ->method('getValue')
            ->with('web/default/admin')
            ->willReturn('default/path');
        $this->assertEquals('default/path', $this->adminPathConfig->getDefaultPath());
    }
}
