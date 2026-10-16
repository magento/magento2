<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Test\Unit\Model\OrderItem;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Item as OrderItemModel;
use Magento\SalesGraphQl\Model\OrderItem\DataProvider;
use Magento\SalesGraphQl\Model\OrderItem\OptionsProcessor;
use Magento\Tax\Helper\Data as TaxHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends TestCase
{
    /**
     * @var OrderItemRepositoryInterface|MockObject
     */
    private $orderItemRepository;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var OptionsProcessor|MockObject
     */
    private $optionsProcessor;

    /**
     * @var TaxHelper|MockObject
     */
    private $taxHelper;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->orderItemRepository = $this->createMock(OrderItemRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->optionsProcessor = $this->createMock(OptionsProcessor::class);
        $this->taxHelper = $this->createMock(TaxHelper::class);

        $this->searchCriteriaBuilder->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')->willReturn($this->createMock(SearchCriteria::class));
        $this->optionsProcessor->method('getItemOptions')
            ->willReturn(['selected_options' => [], 'entered_options' => []]);

        $this->dataProvider = new DataProvider(
            $this->orderItemRepository,
            $this->productRepository,
            $this->orderRepository,
            $this->searchCriteriaBuilder,
            $this->optionsProcessor,
            $this->taxHelper
        );
    }

    public function testProvidedOrderIsNotReloadedFromRepository(): void
    {
        $orderId = 5;
        $orderItemId = 42;
        $orderItem = $this->createOrderItemMock($orderItemId, $orderId, 7);
        $order = $this->createOrderMock($orderId, 'USD');

        $this->stubItemAndProductLists([$orderItem]);
        // The order is already loaded upstream, so it must not be queried again.
        $this->orderRepository->expects($this->never())->method('getList');

        $this->dataProvider->addOrderItemId($orderItemId);
        $this->dataProvider->addOrder($order);
        $result = $this->dataProvider->getOrderItemById($orderItemId);

        $this->assertSame('USD', $result['product_sale_price']['currency']);
    }

    public function testMissingOrderFallsBackToRepository(): void
    {
        $orderId = 9;
        $orderItemId = 77;
        $orderItem = $this->createOrderItemMock($orderItemId, $orderId, 3);
        $order = $this->createOrderMock($orderId, 'EUR');

        $this->stubItemAndProductLists([$orderItem]);
        // No order was provided, so the repository must be queried for the missing id.
        $orderSearchResult = $this->createMock(OrderSearchResultInterface::class);
        $orderSearchResult->method('getItems')->willReturn([$order]);
        $this->orderRepository->expects($this->once())->method('getList')->willReturn($orderSearchResult);

        $this->dataProvider->addOrderItemId($orderItemId);
        $result = $this->dataProvider->getOrderItemById($orderItemId);

        $this->assertSame('EUR', $result['product_sale_price']['currency']);
    }

    /**
     * @param OrderItemModel[] $orderItems
     */
    private function stubItemAndProductLists(array $orderItems): void
    {
        $itemSearchResult = $this->createMock(OrderItemSearchResultInterface::class);
        $itemSearchResult->method('getItems')->willReturn($orderItems);
        $this->orderItemRepository->method('getList')->willReturn($itemSearchResult);

        $productSearchResult = $this->createMock(ProductSearchResultsInterface::class);
        $productSearchResult->method('getItems')->willReturn([]);
        $this->productRepository->method('getList')->willReturn($productSearchResult);
    }

    /**
     * @param int $itemId
     * @param int $orderId
     * @param int $productId
     * @return OrderItemModel|MockObject
     */
    private function createOrderItemMock(int $itemId, int $orderId, int $productId)
    {
        $orderItem = $this->createMock(OrderItemModel::class);
        // getItemId() is a DB value surfaced as a string; the provider base64_encodes it.
        $orderItem->method('getItemId')->willReturn((string)$itemId);
        $orderItem->method('getOrderId')->willReturn($orderId);
        $orderItem->method('getProductId')->willReturn($productId);
        $orderItem->method('getName')->willReturn('Item name');
        $orderItem->method('getSku')->willReturn('sku-' . $itemId);
        $orderItem->method('getProductType')->willReturn('simple');
        $orderItem->method('getChildrenItems')->willReturn([]);
        $orderItem->method('getStatus')->willReturn('Ordered');
        $orderItem->method('getDiscountAmount')->willReturn(0);
        $orderItem->method('getPrice')->willReturn(10.0);
        $orderItem->method('getPriceInclTax')->willReturn(12.0);
        return $orderItem;
    }

    /**
     * @param int $orderId
     * @param string $currency
     * @return OrderInterface|MockObject
     */
    private function createOrderMock(int $orderId, string $currency)
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getEntityId')->willReturn($orderId);
        $order->method('getStoreId')->willReturn(1);
        $order->method('getOrderCurrencyCode')->willReturn($currency);
        $order->method('getDiscountDescription')->willReturn(null);
        $order->method('getDiscountAmount')->willReturn(0);
        return $order;
    }
}
