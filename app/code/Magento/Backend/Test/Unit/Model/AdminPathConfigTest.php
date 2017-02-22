<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\Model\AdminPathConfig;
use Magento\Store\Model\Store;

class AdminPathConfigTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->coreConfig = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false
        );
        $this->backendConfig = $this->getMockForAbstractClass('Magento\Backend\App\ConfigInterface', [], '', false);
        $this->url = $this->getMockForAbstractClass(
            'Magento\Framework\UrlInterface',
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
            'Magento\Framework\App\RequestInterface',
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
     * @param $expected
     * @dataProvider shouldBeSecureDataProvider
     */
    public function testShouldBeSecure($unsecureBaseUrl, $useSecureInAdmin, $secureBaseUrl, $expected)
    {
        $coreConfigValueMap = [
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL, 'default', null, $unsecureBaseUrl],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, 'default', null, $secureBaseUrl],
        ];
        $this->coreConfig->expects($this->any())->method('getValue')->will($this->returnValueMap($coreConfigValueMap));
        $this->backendConfig->expects($this->any())->method('isSetFlag')->willReturn($useSecureInAdmin);
        $this->assertEquals($expected, $this->adminPathConfig->shouldBeSecure(''));
    }

    /**
     * @return array
     */
    public function shouldBeSecureDataProvider()
    {
        return [
            ['http://localhost/', false, 'default', false],
            ['http://localhost/', true, 'default', false],
            ['https://localhost/', false, 'default', true],
            ['https://localhost/', true, 'default', true],
            ['http://localhost/', false, 'https://localhost/', false],
            ['http://localhost/', true, 'https://localhost/', true],
            ['https://localhost/', true, 'https://localhost/', true],
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
