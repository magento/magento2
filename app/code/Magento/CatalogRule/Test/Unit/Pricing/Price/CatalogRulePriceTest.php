<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogRulePriceTest extends TestCase
{
    /**
     * @var CatalogRulePrice
     */
    private $object;

    /**
     * @var Product|MockObject
     */
    private $saleableItemMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $dataTimeMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var Rule|MockObject
     */
    private $catalogRuleResourceMock;

    /**
     * @var WebsiteInterface|MockObject
     */
    private $coreWebsiteMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $coreStoreMock;

    /**
     * @var Calculator|MockObject
     */
    private $calculator;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->dataTimeMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->coreStoreMock = $this->getMockForAbstractClass(StoreInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->coreStoreMock);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->catalogRuleResourceMock = $this->createMock(Rule::class);
        $this->coreWebsiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $this->calculator = $this->createMock(Calculator::class);
        $qty = 1;
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);

        $this->object = new CatalogRulePrice(
            $this->saleableItemMock,
            $qty,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->dataTimeMock,
            $this->storeManagerMock,
            $this->customerSessionMock,
            $this->catalogRuleResourceMock
        );
    }

    /**
     * Test get Value
     */
    public function testGetValue()
    {
        $storeId = 5;
        $coreWebsiteId = 2;
        $productId = 4;
        $customerGroupId = 3;
        $date = new \DateTime();

        $catalogRulePrice = 55.12;
        $convertedPrice = 45.34;

        $this->coreStoreMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->dataTimeMock->expects($this->once())
            ->method('scopeDate')
            ->with($storeId)
            ->willReturn($date);
        $this->coreStoreMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($coreWebsiteId);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);
        $this->catalogRuleResourceMock->expects($this->once())
            ->method('getRulePrice')
            ->with($date, $coreWebsiteId, $customerGroupId, $productId)
            ->willReturn($catalogRulePrice);
        $this->saleableItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($catalogRulePrice)
            ->willReturn($convertedPrice);

        $this->assertEquals($convertedPrice, $this->object->getValue());
    }

    public function testGetValueFromData()
    {
        $catalogRulePrice = 7.1;
        $convertedPrice = 5.84;

        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($catalogRulePrice)
            ->willReturn($convertedPrice);

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
            ->willReturn(false);

        $result = $this->object->getValue();
        $this->assertFalse($result);
    }

    public function testGetValueWithNullAmount()
    {
        $catalogRulePrice = null;
        $convertedPrice = 0.0;

        $this->saleableItemMock->expects($this->once())->method('hasData')
            ->with('catalog_rule_price')->willReturn(true);
        $this->saleableItemMock->expects($this->once())->method('getData')
            ->with('catalog_rule_price')->willReturn($catalogRulePrice);

        $this->assertEquals($convertedPrice, $this->object->getValue());
    }
}
