<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\App\Action;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Test\Unit\Action\Stub\ActionStub;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation\Proxy as TaxCalculation;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\App\Action\ContextPlugin;
use Magento\Weee\Model\Tax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests to cover Context Plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContextPluginTest extends TestCase
{
    /**
     * @var TaxHelper|MockObject
     */
    protected $taxHelperMock;

    /**
     * @var WeeeHelper|MockObject
     */
    protected $weeeHelperMock;

    /**
     * @var Tax|MockObject
     */
    protected $weeeTaxMock;

    /**
     * @var HttpContext|MockObject
     */
    protected $httpContextMock;

    /**
     * @var TaxCalculation|MockObject
     */
    protected $taxCalculationMock;

    /**
     * @var ModuleManager|MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var PageCacheConfig|MockObject
     */
    protected $cacheConfigMock;

    /**
     * @var StoreManager|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Config|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSessionMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ContextPlugin
     */
    protected $contextPlugin;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->taxHelperMock = $this->getMockBuilder(TaxHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeHelperMock = $this->getMockBuilder(WeeeHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeTaxMock = $this->getMockBuilder(Tax::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpContextMock = $this->getMockBuilder(HttpContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getDefaultTaxBillingAddress',
                    'getDefaultTaxShippingAddress',
                    'getCustomerTaxClassId',
                    'getWebsiteId',
                    'isLoggedIn'
                ]
            )
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(ModuleManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(PageCacheConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextPlugin = $this->objectManager->getObject(
            ContextPlugin::class,
            [
                'customerSession' => $this->customerSessionMock,
                'httpContext' => $this->httpContextMock,
                'weeeTax' => $this->weeeTaxMock,
                'taxHelper' => $this->taxHelperMock,
                'weeeHelper' => $this->weeeHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock,
                'storeManager' => $this->storeManagerMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    public function testBeforeExecuteBasedOnDefault()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn(true);

        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->taxHelperMock->expects($this->once())
            ->method('getTaxBasedOn')
            ->willReturn('billing');

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                TaxConfig::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('US');

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                TaxConfig::CONFIG_XML_PATH_DEFAULT_REGION,
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(0);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 0, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with('weee_tax_region', ['countryId' => 'US', 'regionId' => 0], 0);

        /** @var ActionStub $action */
        $action = $this->objectManager->getObject(ActionStub::class);

        $this->contextPlugin->beforeExecute($action);
    }

    public function testBeforeExecuteBasedOnOrigin()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn(true);

        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->taxHelperMock->expects($this->once())
            ->method('getTaxBasedOn')
            ->willReturn('origin');

        /** @var ActionStub $action */
        $action = $this->objectManager->getObject(ActionStub::class);

        $this->contextPlugin->beforeExecute($action);
    }

    public function testBeforeExecuteBasedOnBilling()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn(true);

        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->taxHelperMock->expects($this->once())
            ->method('getTaxBasedOn')
            ->willReturn('billing');

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                TaxConfig::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('US');

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                TaxConfig::CONFIG_XML_PATH_DEFAULT_REGION,
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(0);

        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxBillingAddress')
            ->willReturn(['country_id' => 'US', 'region_id' => 1]);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 1, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with('weee_tax_region', ['countryId' => 'US', 'regionId' => 1], 0);

        /** @var ActionStub $action */
        $action = $this->objectManager->getObject(ActionStub::class);

        $this->contextPlugin->beforeExecute($action);
    }

    public function testBeforeExecuterBasedOnShipping()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn(true);

        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->taxHelperMock->expects($this->once())
            ->method('getTaxBasedOn')
            ->willReturn('shipping');

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                TaxConfig::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn('US');

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                TaxConfig::CONFIG_XML_PATH_DEFAULT_REGION,
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(0);

        $this->customerSessionMock->expects($this->once())
            ->method('getDefaultTaxShippingAddress')
            ->willReturn(['country_id' => 'US', 'region_id' => 1]);

        $this->weeeTaxMock->expects($this->once())
            ->method('isWeeeInLocation')
            ->with('US', 1, 1)
            ->willReturn(true);

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with('weee_tax_region', ['countryId' => 'US', 'regionId' => 1], 0);

        /** @var ActionStub $action */
        $action = $this->objectManager->getObject(ActionStub::class);

        $this->contextPlugin->beforeExecute($action);
    }
}
