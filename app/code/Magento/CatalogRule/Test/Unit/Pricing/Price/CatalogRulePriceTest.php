<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Observer\RulePricesStorage;
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
     * @var RulePricesStorage|\PHPUnit\Framework\MockObject\MockObject
     */
    private $rulePricesStorageMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->dataTimeMock = $this->createMock(TimezoneInterface::class);
        $this->coreStoreMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($this->coreStoreMock);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->catalogRuleResourceMock = $this->createMock(Rule::class);
        $this->coreWebsiteMock = $this->createMock(WebsiteInterface::class);
        $this->calculator = $this->createMock(Calculator::class);
        $qty = 1;
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->rulePricesStorageMock = $this->createMock(RulePricesStorage::class);

        $this->object = new CatalogRulePrice(
            $this->saleableItemMock,
            $qty,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->dataTimeMock,
            $this->storeManagerMock,
            $this->customerSessionMock,
            $this->catalogRuleResourceMock,
            $this->rulePricesStorageMock
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
        $this->rulePricesStorageMock->expects($this->once())
            ->method('hasRulePrice')
            ->willReturn(false);
        $this->rulePricesStorageMock->expects($this->never())
            ->method('setRulePrice');
        $this->catalogRuleResourceMock->expects($this->once())
            ->method('getRulePrice')
            ->with($date, $coreWebsiteId, $customerGroupId, $productId)
            ->willReturn($catalogRulePrice);
        $this->saleableItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($catalogRulePrice, null, null, 4)
            ->willReturn($convertedPrice);

        $this->assertEquals($convertedPrice, $this->object->getValue());
    }

    /**
     * A rule price already loaded for the whole collection is reused instead of
     * being queried again for the individual product being rendered.
     */
    public function testGetValueUsesPreloadedRulePrice()
    {
        $storeId = 5;
        $coreWebsiteId = 2;
        $productId = 4;
        $customerGroupId = 3;
        $date = new \DateTime('2026-01-01 00:00:00');
        $preloadedPrice = 55.12;
        $convertedPrice = 45.34;

        $this->coreStoreMock->method('getId')->willReturn($storeId);
        $this->coreStoreMock->method('getWebsiteId')->willReturn($coreWebsiteId);
        $this->dataTimeMock->method('scopeDate')->with($storeId)->willReturn($date);
        $this->customerSessionMock->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->saleableItemMock->method('getId')->willReturn($productId);

        $expectedKey = '2026-01-01 00:00:00|' . $coreWebsiteId . '|' . $customerGroupId . '|' . $productId;

        $this->rulePricesStorageMock->expects($this->once())
            ->method('hasRulePrice')
            ->with($expectedKey)
            ->willReturn(true);
        $this->rulePricesStorageMock->expects($this->once())
            ->method('getRulePrice')
            ->with($expectedKey)
            ->willReturn($preloadedPrice);

        $this->catalogRuleResourceMock->expects($this->never())->method('getRulePrice');

        $this->priceCurrencyMock->method('convertAndRound')
            ->with($preloadedPrice, null, null, 4)
            ->willReturn($convertedPrice);

        $this->assertEquals($convertedPrice, $this->object->getValue());
    }

    public function testGetValueFromData()
    {
        $catalogRulePrice = 7.1;
        $convertedPrice = 5.84;

        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($catalogRulePrice, null, null, 4)
            ->willReturn($convertedPrice);

        $this->saleableItemMock->expects($this->once())->method('hasData')
            ->with('catalog_rule_price')->willReturn(true);
        $this->saleableItemMock->expects($this->once())->method('getData')
            ->with('catalog_rule_price')->willReturn($catalogRulePrice);

        $this->assertEquals($convertedPrice, $this->object->getValue());
    }

    public function testGetAmountNoBaseAmount()
    {
        $this->dataTimeMock->method('scopeDate')->willReturn(new \DateTime());
        $this->catalogRuleResourceMock->expects($this->once())
            ->method('getRulePrice')
            ->willReturn(false);

        $result = $this->object->getValue();
        $this->assertFalse($result);
    }

    public function testGetValueWithNullAmount()
    {
        $this->dataTimeMock->method('scopeDate')->willReturn(new \DateTime());
        $catalogRulePrice = null;
        $convertedPrice = 0.0;

        $this->saleableItemMock->expects($this->once())->method('hasData')
            ->with('catalog_rule_price')->willReturn(true);
        $this->saleableItemMock->expects($this->once())->method('getData')
            ->with('catalog_rule_price')->willReturn($catalogRulePrice);

        $this->assertEquals($convertedPrice, $this->object->getValue());
    }
}
