<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\Model\AdminPathConfig;
use Magento\Store\Model\Store;

class AdminPathConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreConfig;

    /**
     * @var \Magento\Backend\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendConfig;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $url;

    /**
     * @var AdminPathConfig
     */
    protected $adminPathConfig;

    protected function setUp()
    {
        $this->coreConfig = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            '',
            false
        );
        $this->backendConfig = $this->getMockForAbstractClass(
            \Magento\Backend\App\ConfigInterface::class,
            [],
            '',
            false
        );
        $this->url = $this->getMockForAbstractClass(
            \Magento\Framework\UrlInterface::class,
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
            \Magento\Framework\App\RequestInterface::class,
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
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL, 'default', null, $unsecureBaseUrl],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, 'default', null, $secureBaseUrl],
            ['admin/url/custom', 'default', null, $customUrl],
        ]);
        $backendConfigFlagsMap = $this->returnValueMap([
            [\Magento\Store\Model\Store::XML_PATH_SECURE_IN_ADMINHTML, $useSecureInAdmin],
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
    public function shouldBeSecureDataProvider()
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
