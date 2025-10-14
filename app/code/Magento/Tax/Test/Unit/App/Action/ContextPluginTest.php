<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\App\Action;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Test\Unit\Action\Stub\ActionStub;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\App\Action\ContextPlugin as TaxContextPlugin;
use Magento\Tax\Model\Calculation;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\Tax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests to cover ContextPlugin
 */
class ContextPluginTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

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
     * @var Calculation|MockObject
     */
    protected $taxCalculationMock;

    /**
     * Module manager
     *
     * @var ModuleManager|MockObject
     */
    private $moduleManagerMock;

    /**
     * Cache config
     *
     * @var PageCacheConfig|MockObject
     */
    private $cacheConfigMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var TaxContextPlugin
     */
    protected $contextPlugin;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);

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

        $this->taxCalculationMock = $this->getMockBuilder(Calculation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTaxRates'])
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isLoggedIn'])
            ->addMethods(
                [
                    'getDefaultTaxBillingAddress', 'getDefaultTaxShippingAddress', 'getCustomerTaxClassId',
                    'getWebsiteId'
                ]
            )
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(ModuleManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(PageCacheConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextPlugin = $this->objectManager->getObject(
            TaxContextPlugin::class,
            [
                'customerSession' => $this->customerSessionMock,
                'httpContext' => $this->httpContextMock,
                'calculation' => $this->taxCalculationMock,
                'weeeTax' => $this->weeeTaxMock,
                'taxHelper' => $this->taxHelperMock,
                'weeeHelper' => $this->weeeHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock
            ]
        );
    }

    /**
     * @param bool $cache
     * @param bool $taxEnabled
     * @param bool $loggedIn
     * @dataProvider beforeExecuteDataProvider
     */
    public function testBeforeExecute($cache, $taxEnabled, $loggedIn)
    {
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn($loggedIn);

        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn($cache);

        $this->cacheConfigMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($cache);

        if ($cache && $loggedIn) {
            $this->taxHelperMock->expects($this->any())
                ->method('isCatalogPriceDisplayAffectedByTax')
                ->willReturn($taxEnabled);

            if ($taxEnabled) {
                $this->customerSessionMock->expects($this->once())
                    ->method('getDefaultTaxBillingAddress')
                    ->willReturn(['country_id' => 1, 'region_id' => 1, 'postcode' => 11111]);
                $this->customerSessionMock->expects($this->once())
                    ->method('getDefaultTaxShippingAddress')
                    ->willReturn(['country_id' => 1, 'region_id' => 1, 'postcode' => 11111]);
                $this->customerSessionMock->expects($this->once())
                    ->method('getCustomerTaxClassId')
                    ->willReturn(1);

                $this->taxCalculationMock->expects($this->once())
                    ->method('getTaxRates')
                    ->with(
                        ['country_id' => 1, 'region_id' => 1, 'postcode' => 11111],
                        ['country_id' => 1, 'region_id' => 1, 'postcode' => 11111],
                        1
                    )
                    ->willReturn([]);

                $this->httpContextMock->expects($this->any())
                    ->method('setValue')
                    ->with('tax_rates', [], 0);
            }

            $action = $this->objectManager->getObject(ActionStub::class);
            $result = $this->contextPlugin->beforeExecute($action);
            $this->assertNull($result);
        } else {
            $this->assertFalse($loggedIn);
        }
    }

    /**
     * @return array
     */
    public static function beforeExecuteDataProvider()
    {
        return [
            [false, false, false],
            [true, true, false],
            [true, true, true],
            [true, false, true],
            [true, true, true]
        ];
    }
}
