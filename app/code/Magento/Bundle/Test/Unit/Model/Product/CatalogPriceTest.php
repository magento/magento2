<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Model\Product\CatalogPrice;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogPriceTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var CatalogPrice
     */
    protected $catalogPrice;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $commonPriceMock;

    /**
     * @var MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var FinalPrice|MockObject
     */
    protected $priceModelMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->commonPriceMock = $this->createMock(\Magento\Catalog\Model\Product\CatalogPrice::class);
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['setStoreId', 'setWebsiteId', 'setCustomerGroupId', 'setPriceModel',
             'getStoreId', 'getWebsiteId', 'getCustomerGroupId', 'getPriceModel']
        );
        $this->priceModelMock = $this->createPartialMockWithReflection(
            Price::class,
            ['setTotalPrices', 'getTotalPrices']
        );
        $this->catalogPrice = new CatalogPrice(
            $this->storeManagerMock,
            $this->commonPriceMock,
            $this->coreRegistryMock
        );
    }

    /**
     * @return void
     */
    public function testGetCatalogPriceWithCurrentStore(): void
    {
        $this->coreRegistryMock->expects($this->once())->method('unregister')->with('rule_data');
        $this->productMock->method('getStoreId')->willReturn('store_id');
        $this->productMock->method('getWebsiteId')->willReturn('website_id');
        $this->productMock->method('getCustomerGroupId')->willReturn('group_id');
        $this->coreRegistryMock->expects($this->once())->method('register');
        $this->productMock->method('getPriceModel')->willReturn($this->priceModelMock);
        $this->priceModelMock->method('getTotalPrices')->willReturn(15);
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeManagerMock->expects($this->never())->method('setCurrentStore');
        $this->assertEquals(15, $this->catalogPrice->getCatalogPrice($this->productMock));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetCatalogPriceWithCustomStore(): void
    {
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn('store_id');
        $currentStoreMock = $this->createMock(StoreInterface::class);
        $currentStoreMock->expects($this->once())->method('getId')->willReturn('current_store_id');

        $this->coreRegistryMock->expects($this->once())->method('unregister')->with('rule_data');
        $this->productMock->method('getStoreId')->willReturn('store_id');
        $this->productMock->method('getWebsiteId')->willReturn('website_id');
        $this->productMock->method('getCustomerGroupId')->willReturn('group_id');
        $this->coreRegistryMock->expects($this->once())->method('register');
        $this->productMock->method('getPriceModel')->willReturn($this->priceModelMock);
        $this->priceModelMock->method('getTotalPrices')->willReturn(15);

        $this->storeManagerMock
            ->method('getStore')
            ->willReturn($currentStoreMock);
        $this->storeManagerMock
            ->method('setCurrentStore')
            ->willReturnCallback(function ($arg) {
                if ($arg == 'store_id' || $arg == 'current_store_id') {
                    return null;
                }
            });

        $this->assertEquals(15, $this->catalogPrice->getCatalogPrice($this->productMock, $storeMock, true));
    }

    /**
     * @return void
     */
    public function testGetCatalogRegularPrice(): void
    {
        $this->assertNull($this->catalogPrice->getCatalogRegularPrice($this->productMock));
    }
}
