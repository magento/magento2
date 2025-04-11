<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\App\Area;

use Laminas\Uri\Uri;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\App\Config;
use Magento\Backend\Setup\ConfigOptionsList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontNameResolverTest extends TestCase
{
    /**
     * @var FrontNameResolver
     */
    protected $model;

    /**
     * @var MockObject|Config
     */
    protected $configMock;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject|Uri
     */
    protected $uri;

    /**
     * @var MockObject|Http
     */
    protected $request;

    /**
     * @var string
     */
    protected $_defaultFrontName = 'defaultFrontName';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        /** @var MockObject|DeploymentConfig $deploymentConfigMock */
        $deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME)
            ->willReturn($this->_defaultFrontName);
        $this->uri = $this->createPartialMock(Uri::class, ['parse']);
        $this->request = $this->createMock(Http::class);
        $this->configMock = $this->createMock(Config::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->model = new FrontNameResolver(
            $this->configMock,
            $deploymentConfigMock,
            $this->scopeConfigMock,
            $this->uri,
            $this->request
        );
    }

    /**
     * @return void
     */
    public function testIfCustomPathUsed(): void
    {
        $this->configMock
            ->method('getValue')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['admin/url/use_custom_path'] => true,
                ['admin/url/custom_path'] => 'expectedValue'
            });
        $this->assertEquals('expectedValue', $this->model->getFrontName());
    }

    /**
     * @return void
     */
    public function testIfCustomPathNotUsed(): void
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'admin/url/use_custom_path'
        )->willReturn(
            false
        );
        $this->assertEquals($this->_defaultFrontName, $this->model->getFrontName());
    }

    /**
     * @param string $url
     * @param string|null $host
     * @param bool $isHttps
     * @param string $useCustomAdminUrl
     * @param string $customAdminUrl
     * @param bool $expectedValue
     *
     * @return void
     * @dataProvider hostsDataProvider
     */
    public function testIsHostBackend(
        string $url,
        ?string $host,
        bool $isHttps,
        string $useCustomAdminUrl,
        string $customAdminUrl,
        bool $expectedValue
    ): void {
        $this->scopeConfigMock->method('getValue')
            ->willReturnMap(
                [
                    [Store::XML_PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE, null, $url],
                    [Store::XML_PATH_SECURE_BASE_URL, ScopeInterface::SCOPE_STORE, null, $url],
                    [
                        FrontNameResolver::XML_PATH_USE_CUSTOM_ADMIN_URL,
                        ScopeInterface::SCOPE_STORE,
                        null,
                        $useCustomAdminUrl
                    ],
                    [
                        FrontNameResolver::XML_PATH_CUSTOM_ADMIN_URL,
                        ScopeInterface::SCOPE_STORE,
                        null,
                        $customAdminUrl
                    ]
                ]
            );

        $this->request->expects($this->atLeastOnce())
            ->method('getServer')
            ->willReturnMap(
                [
                    ['HTTP_HOST', null, $host],
                ]
            );
        $this->request->method('isSecure')
            ->willReturn($isHttps);

        $this->uri->method('parse')
            ->willReturnCallback(
                fn ($url) => $this->uri->setScheme(parse_url($url, PHP_URL_SCHEME))
                    ->setHost(parse_url($url, PHP_URL_HOST))
                    ->setPort(parse_url($url, PHP_URL_PORT))
            );
        $this->assertEquals($expectedValue, $this->model->isHostBackend());
    }

    /**
     * Test the case when backend url is not set.
     *
     * @return void
     */
    public function testIsHostBackendWithEmptyHost(): void
    {
        $this->request->expects($this->any())
            ->method('getServer')
            ->willReturn('magento2.loc');

        $this->assertFalse($this->model->isHostBackend());
    }

    /**
     * @return array
     */
    public static function hostsDataProvider(): array
    {
        return [
            'withoutPort' => [
                'url' => 'http://magento2.loc/',
                'host' => 'magento2.loc',
                'isHttps' => false,
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => true
            ],
            'withPort' => [
                'url' => 'http://magento2.loc:8080/',
                'host' => 'magento2.loc:8080',
                'isHttps' => false,
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => true
            ],
            'withStandartPortInUrlWithoutPortInHost' => [
                'url' => 'http://magento2.loc:80/',
                'host' => 'magento2.loc',
                'isHttps' => false,
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => true
            ],
            'withoutStandartPortInUrlWithPortInHost' => [
                'url' => 'https://magento2.loc/',
                'host' => 'magento2.loc:443',
                'isHttps' => true,
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => true
            ],
            'differentHosts' => [
                'url' => 'http://m2.loc/',
                'host' => 'magento2.loc',
                'isHttps' => false,
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => false
            ],
            'differentPortsOnOneHost' => [
                'url' => 'http://magento2.loc/',
                'host' => 'magento2.loc:8080',
                'isHttps' => false,
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => false
            ],
            'withCustomAdminUrl' => [
                'url' => 'http://magento2.loc/',
                'host' => 'myhost.loc',
                'isHttps' => true,
                'useCustomAdminUrl' => '1',
                'customAdminUrl' => 'https://myhost.loc/',
                'expectedValue' => true
            ],
            'withCustomAdminUrlWrongHost' => [
                'url' => 'http://magento2.loc/',
                'host' => 'SomeOtherHost.loc',
                'isHttps' => false,
                'useCustomAdminUrl' => '1',
                'customAdminUrl' => 'https://myhost.loc/',
                'expectedValue' => false
            ],
            'withEmptyHost' => [
                'url' => 'http://magento2.loc/',
                'host' => null,
                'isHttps' => false,
                'useCustomAdminUrl' => '0',
                'customAdminUrl' => '',
                'expectedValue' => false
            ]
        ];
    }
}
