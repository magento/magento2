<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Pricing\Price;

use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CatalogRulePriceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogRulePriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Pricing\Price\CatalogRulePrice
     */
    protected $object;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataTimeMock;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogRuleResourceFactoryMock;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogRuleResourceMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreWebsiteMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreStoreMock;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculator;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->saleableItemMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getId', '__wakeup', 'getPriceInfo', 'hasData', 'getData'],
            [],
            '',
            false
        );
        $this->dataTimeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->coreStoreMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->coreStoreMock));

        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->priceInfoMock = $this->getMock(
            \Magento\Framework\Pricing\PriceInfo::class,
            ['getAdjustments'],
            [],
            '',
            false
        );
        $this->catalogRuleResourceFactoryMock = $this->getMock(
            \Magento\CatalogRule\Model\ResourceModel\RuleFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->catalogRuleResourceMock = $this->getMock(
            \Magento\CatalogRule\Model\ResourceModel\Rule::class,
            [],
            [],
            '',
            false
        );

        $this->coreWebsiteMock = $this->getMock(\Magento\Store\Model\Website::class, [], [], '', false);

        $this->priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue([]));
        $this->saleableItemMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));

        $this->catalogRuleResourceFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->catalogRuleResourceMock));

        $this->calculator = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\Calculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $qty = 1;

        $this->priceCurrencyMock = $this->getMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->object = new CatalogRulePrice(
            $this->saleableItemMock,
            $qty,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->dataTimeMock,
            $this->storeManagerMock,
            $this->customerSessionMock,
            $this->catalogRuleResourceFactoryMock
        );

        (new ObjectManager($this))->setBackwardCompatibleProperty(
            $this->object,
            'ruleResource',
            $this->catalogRuleResourceMock
        );
    }

    /**
     * Test get Value
     */
    public function testGetValue()
    {
        $coreStoreId = 1;
        $coreWebsiteId = 1;
        $productId = 1;
        $customerGroupId = 1;
        $dateTime = time();

        $catalogRulePrice = 55.12;
        $convertedPrice = 45.34;

        $this->coreStoreMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($coreStoreId));
        $this->coreStoreMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($coreWebsiteId));
        $this->dataTimeMock->expects($this->once())
            ->method('scopeDate')
            ->with($this->equalTo($coreStoreId))
            ->will($this->returnValue($dateTime));
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue($customerGroupId));
        $this->catalogRuleResourceMock->expects($this->once())
            ->method('getRulePrice')
            ->will($this->returnValue($catalogRulePrice));
        $this->saleableItemMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($productId));
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($catalogRulePrice)
            ->will($this->returnValue($convertedPrice));

        $this->assertEquals($convertedPrice, $this->object->getValue());
    }

    public function testGetValueFromData()
    {
        $catalogRulePrice = 7.1;
        $convertedPrice = 5.84;

        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($catalogRulePrice)
            ->will($this->returnValue($convertedPrice));

        $this->saleableItemMock->expects($this->once())->method('hasData')
            ->with('catalog_rule_price')->willReturn(true);
        $this->saleableItemMock->expects($this->once())->method('getData')
            ->with('catalog_rule_price')->willReturn($catalogRulePrice);

        $this->assertEquals($convertedPrice, $this->object->getValue());
    }

    public function testGetAmountNoBaseAmount()
    {
        $this->catalogRuleResourceMock->expects($this->once())
            ->method('getRulePrice')
            ->will($this->returnValue(false));

        $result = $this->object->getValue();
        $this->assertFalse($result);
    }
}
