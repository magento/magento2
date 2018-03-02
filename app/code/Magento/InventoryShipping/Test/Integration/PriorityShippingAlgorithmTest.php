<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Test\Integration;

use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryShipping\Model\PriorityShippingAlgorithm\PriorityShippingAlgorithm;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class PriorityShippingAlgorithmTest extends TestCase
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var OrderItemInterfaceFactory $orderItemFactory
     */
    private $orderItemFactory;

    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var PriorityShippingAlgorithm
     */
    private $shippingAlgorithm;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->shippingAlgorithm = Bootstrap::getObjectManager()->get(PriorityShippingAlgorithm::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
        $this->orderFactory = Bootstrap::getObjectManager()->get(OrderInterfaceFactory::class);
        $this->orderItemFactory = Bootstrap::getObjectManager()->get(OrderItemInterfaceFactory::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testDefaultStockSource()
    {
        $order = $this->createOrder([
            'SKU-1' => 1,
            'SKU-2' => 1,
            'SKU-3' => 1,
        ]);

        $algorithmResult = $this->shippingAlgorithm->execute($order);
        self::assertEquals($algorithmResult->isShippable(), true);

        $sourceSelections = $algorithmResult->getSourceSelections();
        self::assertCount(1, $sourceSelections);

        $defaultSourceSelection = reset($sourceSelections);
        self::assertEquals($this->defaultSourceProvider->getCode(), $defaultSourceSelection->getSourceCode());

        $sourceItemSelections = $defaultSourceSelection->getSourceItemSelections();
        self::assertCount(3, $sourceItemSelections);

        self::assertEquals('SKU-1', $sourceItemSelections[0]->getSku());
        self::assertEquals(1, $sourceItemSelections[0]->getQty());
        self::assertEquals(5.5, $sourceItemSelections[0]->getQtyAvailable());

        self::assertEquals('SKU-2', $sourceItemSelections[1]->getSku());
        self::assertEquals(1, $sourceItemSelections[1]->getQty());
        self::assertEquals(5, $sourceItemSelections[1]->getQtyAvailable());

        self::assertEquals('SKU-3', $sourceItemSelections[2]->getSku());
        self::assertEquals(1, $sourceItemSelections[2]->getQty());
        self::assertEquals(6, $sourceItemSelections[2]->getQtyAvailable());
    }

    /**
     * @return array
     */
    public function stockSourceCombinationDataProvider(): array
    {
        return [
            [
                'store_code' => 'store_for_eu_website',
                'order_data' => [
                    'SKU-1' => 14.5,
                    'SKU-3' => 3,
                ],
                'expected_result' => [
                    [
                        'source_code' => 'eu-1',
                        'source_item_selections' => [
                            ['SKU-1', 5.5, 5.5],
                        ],
                    ],
                    [
                        'source_code' => 'eu-2',
                        'source_item_selections' => [
                            ['SKU-1', 3, 3],
                            ['SKU-3', 3, 6],
                        ],
                    ],
                    [
                        'source_code' => 'eu-3',
                        'source_item_selections' => [
                            ['SKU-1', 6, 10],
                        ],
                    ],
                ],
            ],
            [
                'store_code' => 'store_for_global_website',
                'order_data' => [
                    'SKU-1' => 14.5,
                    'SKU-3' => 3,
                ],
                'expected_result' => [
                    [
                        'source_code' => 'eu-3',
                        'source_item_selections' => [
                            ['SKU-1', 10, 10],
                        ],
                    ],
                    [
                        'source_code' => 'eu-2',
                        'source_item_selections' => [
                            ['SKU-1', 3, 3],
                            ['SKU-3', 3, 6],
                        ],
                    ],
                    [
                        'source_code' => 'eu-1',
                        'source_item_selections' => [
                            ['SKU-1', 1.5, 5.5],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Tests source selections with different source-stock link priorities.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @dataProvider stockSourceCombinationDataProvider
     * @param string $storeCode
     * @param array $orderData
     * @param array $expectedResult
     */
    public function testStockSourceCombination(string $storeCode, array $orderData, array $expectedResult)
    {
        $order = $this->createOrder(
            $orderData,
            $storeCode
        );
        $algorithmResult = $this->shippingAlgorithm->execute($order);

        $sourceSelections = $algorithmResult->getSourceSelections();
        self::assertCount(count($expectedResult), $sourceSelections);
        self::assertEquals($algorithmResult->isShippable(), true);

        foreach ($expectedResult as $i => $expectedSourceSelection) {
            $sourceSelection = $sourceSelections[$i];
            self::assertEquals($expectedSourceSelection['source_code'], $sourceSelection->getSourceCode());

            $sourceItemSelections = $sourceSelection->getSourceItemSelections();
            self::assertCount(count($expectedSourceSelection['source_item_selections']), $sourceItemSelections);

            foreach ($expectedSourceSelection['source_item_selections'] as $j => $expectedSourceItemSelection) {
                $sourceItemSelection = $sourceItemSelections[$j];
                self::assertEquals($expectedSourceItemSelection[0], $sourceItemSelection->getSku());
                self::assertEquals($expectedSourceItemSelection[1], $sourceItemSelection->getQty());
                self::assertEquals($expectedSourceItemSelection[2], $sourceItemSelection->getQtyAvailable());
            }
        }
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testStockSourceCombinationNoShippable()
    {
        $order = $this->createOrder([
            'SKU-1' => 50.0
        ]);

        $algorithmResult = $this->shippingAlgorithm->execute($order);
        self::assertEquals($algorithmResult->isShippable(), false);
    }

    /**
     * Returns order object with specified products
     *
     * @param array $productsQty
     * @param string $storeCode
     * @return OrderInterface
     */
    private function createOrder(array $productsQty, string $storeCode = 'default'): OrderInterface
    {
        $orderItems = [];
        foreach ($productsQty as $sku => $qty) {
            $orderItem = $this->orderItemFactory->create();
            $orderItem->setQtyOrdered($qty);
            $orderItem->setSku($sku);
            $orderItem->setDeleted(false);

            $orderItems[] = $orderItem;
        }

        /** @var OrderInterface $order */
        $order = Bootstrap::getObjectManager()->create(OrderInterface::class);
        $storeId = $this->storeManager->getStore($storeCode)->getId();
        $order->setStoreId($storeId);
        $order->setItems($orderItems);
        return $order;
    }
}
