<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->storeManager = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false
        );
        $this->urlBuilder = $this->getMockForAbstractClass(
            'Magento\Framework\UrlInterface',
            [],
            '',
            false
        );
        $this->scopeConfig = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false
        );
        $this->store = $this->getMockForAbstractClass(
            'Magento\Store\Api\Data\StoreInterface',
            [],
            '',
            false,
            false,
            true,
            ['getBaseUrl']
        );

        $this->provider = new ConfigProvider(
            $this->urlBuilder,
            $this->storeManager,
            $this->scopeConfig
        );
    }

    public function testGetConfigWithoutRedirect()
    {
        $loginUrl = 'http://url.test/customer/login';
        $baseUrl = 'http://base-url.test';

        $this->urlBuilder->expects($this->exactly(2))
            ->method('getUrl')
            ->with(Url::ROUTE_ACCOUNT_LOGIN)
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

        $this->urlBuilder->expects($this->exactly(2))
            ->method('getUrl')
            ->with(Url::ROUTE_ACCOUNT_LOGIN)
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
