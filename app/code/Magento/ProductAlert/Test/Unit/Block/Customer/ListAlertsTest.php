<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Block\Customer;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductAlert\Block\Customer\ListAlerts;
use Magento\ProductAlert\Helper\Data as ProductAlertHelper;
use Magento\ProductAlert\Model\ResourceModel\Price\Collection as PriceAlertCollection;
use Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory as PriceAlertCollectionFactory;
use Magento\ProductAlert\Model\ResourceModel\Stock\Collection as StockAlertCollection;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory as StockAlertCollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\ProductAlert\Block\Customer\ListAlerts
 */
class ListAlertsTest extends TestCase
{
    /**
     * @var ListAlerts
     */
    private $block;

    /**
     * @var ProductAlertHelper|MockObject
     */
    private $productAlertHelperMock;

    /**
     * @var PriceAlertCollection|MockObject
     */
    private $priceCollectionMock;

    /**
     * @var StockAlertCollection|MockObject
     */
    private $stockCollectionMock;

    /**
     * @var PostHelper|MockObject
     */
    private $postHelperMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->productAlertHelperMock = $this->createMock(ProductAlertHelper::class);
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getCustomerId')->willReturn(1);

        $this->priceCollectionMock = $this->createMock(PriceAlertCollection::class);
        $this->priceCollectionMock->method('addFieldToFilter')->willReturnSelf();
        $this->priceCollectionMock->method('addWebsiteFilter')->willReturnSelf();
        $this->priceCollectionMock->method('setOrder')->willReturnSelf();
        $priceCollectionFactoryMock = $this->createMock(PriceAlertCollectionFactory::class);
        $priceCollectionFactoryMock->method('create')->willReturn($this->priceCollectionMock);

        $this->stockCollectionMock = $this->createMock(StockAlertCollection::class);
        $this->stockCollectionMock->method('addFieldToFilter')->willReturnSelf();
        $this->stockCollectionMock->method('addWebsiteFilter')->willReturnSelf();
        $this->stockCollectionMock->method('setOrder')->willReturnSelf();
        $stockCollectionFactoryMock = $this->createMock(StockAlertCollectionFactory::class);
        $stockCollectionFactoryMock->method('create')->willReturn($this->stockCollectionMock);

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $storeMock->method('getId')->willReturn(1);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $storeManagerMock->method('getStore')->willReturn($storeMock);

        $this->postHelperMock = $this->createMock(PostHelper::class);

        $this->block = $objectManager->getObject(
            ListAlerts::class,
            [
                'context' => $this->createMock(Context::class),
                'customerSession' => $sessionMock,
                'priceAlertCollectionFactory' => $priceCollectionFactoryMock,
                'stockAlertCollectionFactory' => $stockCollectionFactoryMock,
                'productCollectionFactory' => $this->createMock(ProductCollectionFactory::class),
                'storeManager' => $storeManagerMock,
                'productAlertHelper' => $this->productAlertHelperMock,
                'priceCurrency' => $this->createMock(PriceCurrencyInterface::class),
                'postDataHelper' => $this->postHelperMock,
            ]
        );
    }

    public function testHasPriceAlertsWhenAllowedAndCollectionNotEmpty(): void
    {
        $this->productAlertHelperMock->method('isPriceAlertAllowed')->willReturn(true);
        $this->priceCollectionMock->method('getSize')->willReturn(2);
        $this->assertTrue($this->block->hasPriceAlerts());
    }

    public function testHasPriceAlertsWhenDisallowed(): void
    {
        $this->productAlertHelperMock->method('isPriceAlertAllowed')->willReturn(false);
        $this->assertFalse($this->block->hasPriceAlerts());
    }

    public function testHasStockAlertsWhenAllowedAndCollectionNotEmpty(): void
    {
        $this->productAlertHelperMock->method('isStockAlertAllowed')->willReturn(true);
        $this->stockCollectionMock->method('getSize')->willReturn(1);
        $this->assertTrue($this->block->hasStockAlerts());
    }

    public function testHasStockAlertsWhenEmpty(): void
    {
        $this->productAlertHelperMock->method('isStockAlertAllowed')->willReturn(true);
        $this->stockCollectionMock->method('getSize')->willReturn(0);
        $this->assertFalse($this->block->hasStockAlerts());
    }

    public function testGetUnsubscribePriceUrl(): void
    {
        $block = $this->getMockBuilder(ListAlerts::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl'])
            ->getMock();
        $block->method('getUrl')->willReturnCallback(static function (string $route, array $params = []) {
            return $route . (isset($params['product']) ? '/' . $params['product'] : '');
        });

        $this->assertSame('productalert/unsubscribe/price/9', $block->getUnsubscribePriceUrl(9));
        $this->assertSame('productalert/unsubscribe/priceAll', $block->getUnsubscribeAllPriceUrl());
        $this->assertSame('productalert/unsubscribe/stockAll', $block->getUnsubscribeAllStockUrl());
    }
}
