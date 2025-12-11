<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\AdminPathConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdminPathConfigTest extends TestCase
{
    use MockCreationTrait;

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
        $this->coreConfig = $this->createStub(ScopeConfigInterface::class);
        $this->backendConfig = $this->createStub(ConfigInterface::class);
        $this->url = $this->createStub(UrlInterface::class);
        $this->adminPathConfig = new AdminPathConfig($this->coreConfig, $this->backendConfig, $this->url);
    }

    public function testGetCurrentSecureUrl()
    {
        $request = $this->createPartialMockWithReflection(Http::class, ['getPathInfo']);
        $request->method('getPathInfo')->willReturn('/info');
        $this->url->method('getBaseUrl')->willReturn('localhost/');
        $this->assertEquals('localhost/info', $this->adminPathConfig->getCurrentSecureUrl($request));
    }

    /**
     * @param $unsecureBaseUrl
     * @param $useSecureInAdmin
     * @param $secureBaseUrl
     * @param $useCustomUrl
     * @param $customUrl
     * @param $expected
     */
    #[DataProvider('shouldBeSecureDataProvider')]
    public function testShouldBeSecure(
        $unsecureBaseUrl,
        $useSecureInAdmin,
        $secureBaseUrl,
        $useCustomUrl,
        $customUrl,
        $expected
    ) {
        $this->coreConfig->method('getValue')->willReturnMap([
            [Store::XML_PATH_UNSECURE_BASE_URL, 'default', null, $unsecureBaseUrl],
            [Store::XML_PATH_SECURE_BASE_URL, 'default', null, $secureBaseUrl],
            ['admin/url/custom', 'default', null, $customUrl],
        ]);
        $this->backendConfig->method('isSetFlag')->willReturnMap([
            [Store::XML_PATH_SECURE_IN_ADMINHTML, $useSecureInAdmin],
            ['admin/url/use_custom', $useCustomUrl],
        ]);
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
        $this->backendConfig->method('getValue')->willReturn('default/path');
        $this->assertEquals('default/path', $this->adminPathConfig->getDefaultPath());
    }
}
