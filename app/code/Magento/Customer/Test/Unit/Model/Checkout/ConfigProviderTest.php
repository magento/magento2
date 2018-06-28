<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Checkout;

use Magento\Customer\Model\Checkout\ConfigProvider;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Url;
use Magento\Customer\Model\Form;
use Magento\Store\Model\ScopeInterface;

class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigProvider
     */
    protected $provider;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerUrl;

    protected function setUp()
    {
        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->urlBuilder = $this->getMockForAbstractClass(
            \Magento\Framework\UrlInterface::class,
            [],
            '',
            false
        );

        $this->scopeConfig = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            '',
            false
        );
        $this->store = $this->getMockForAbstractClass(
            \Magento\Store\Api\Data\StoreInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getBaseUrl']
        );

        $this->customerUrl = $this->createMock(\Magento\Customer\Model\Url::class);

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
