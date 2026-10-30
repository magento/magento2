<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Checkout;

use Magento\Customer\Model\Checkout\ConfigProvider;
use Magento\Customer\Model\Form;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /**
     * @var ConfigProvider
     */
    protected $provider;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var Store|MockObject
     */
    protected $store;

    /**
     * @var Url|MockObject
     */
    private $customerUrl;

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(
            StoreManagerInterface::class
        );

        $this->urlBuilder = $this->createMock(
            UrlInterface::class
        );

        $this->scopeConfig = $this->createMock(
            ScopeConfigInterface::class
        );
        $this->store = $this->createPartialMock(
            Store::class,
            ['getBaseUrl']
        );

        $this->customerUrl = $this->createMock(Url::class);

        $this->provider = new ConfigProvider(
            $this->urlBuilder,
            $this->storeManager,
            $this->scopeConfig,
            $this->customerUrl
        );
    }

    public function testGetConfigWithoutRedirect()
    {
        $loginUrl = 'http://url.test/customer/login';
        $baseUrl = 'http://base-url.test';

        $this->customerUrl->expects($this->exactly(2))
            ->method('getLoginUrl')
            ->willReturn($loginUrl);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(Form::XML_PATH_ENABLE_AUTOCOMPLETE, ScopeInterface::SCOPE_STORE)
            ->willReturn(1);
        $this->assertEquals(
            [
                'customerLoginUrl' => $loginUrl,
                'isRedirectRequired' => true,
                'autocomplete' => 'on',
            ],
            $this->provider->getConfig()
        );
    }

    public function testGetConfig()
    {
        $loginUrl = 'http://base-url.test/customer/login';
        $baseUrl = 'http://base-url.test';

        $this->customerUrl->expects($this->exactly(2))
            ->method('getLoginUrl')
            ->willReturn($loginUrl);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(Form::XML_PATH_ENABLE_AUTOCOMPLETE, ScopeInterface::SCOPE_STORE)
            ->willReturn(0);
        $this->assertEquals(
            [
                'customerLoginUrl' => $loginUrl,
                'isRedirectRequired' => false,
                'autocomplete' => 'off',
            ],
            $this->provider->getConfig()
        );
    }
}
