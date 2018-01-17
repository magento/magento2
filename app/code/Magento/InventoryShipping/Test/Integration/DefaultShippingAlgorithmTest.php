<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Test\Integration;

use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryShipping\Model\DefaultShippingAlgorithm;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DefaultShippingAlgorithmTest extends TestCase
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
     * @var DefaultShippingAlgorithm
     */
    private $shippingAlgorithm;

    protected function setUp()
    {
        $this->shippingAlgorithm = Bootstrap::getObjectManager()->get(DefaultShippingAlgorithm::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
        $this->orderFactory = Bootstrap::getObjectManager()->get(OrderInterfaceFactory::class);
        $this->orderItemFactory = Bootstrap::getObjectManager()->get(OrderItemInterfaceFactory::class);
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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testStockSourceCombination()
    {
        $expectedResult = [
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
                    ['SKU-1', 10, 10],
                ],
            ],
            [
                'source_code' => 'eu-disabled',
                'source_item_selections' => [
                    ['SKU-1', 9, 10],
                ],
            ],
            [
                'source_code' => 'us-1',
                'source_item_selections' => [
                    ['SKU-2', 4.5, 5],
                ],
            ],
        ];

        $order = $this->createOrder([
            'SKU-1' => 27.5,
            'SKU-2' => 4.5,
            'SKU-3' => 3,
        ]);
        $algorithmResult = $this->shippingAlgorithm->execute($order);

        $sourceSelections = $algorithmResult->getSourceSelections();
        self::assertCount(count($expectedResult), $sourceSelections);

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
     * Returns order object with specified products
     *
     * @param array $productsQty
     * @return OrderInterface
     */
    private function createOrder(array $productsQty): OrderInterface
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
        $order->setItems($orderItems);
        return $order;
    }
}
