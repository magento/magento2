<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Test\Integration\Model;

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
     * @var string
     */
    private $defaultSourceCode;

    /**
     * @var OrderItemInterfaceFactory $orderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var DefaultShippingAlgorithm
     */
    private $shippingAlgorithm;

    protected function setUp()
    {
        $this->shippingAlgorithm = Bootstrap::getObjectManager()->create(DefaultShippingAlgorithm::class);

        /** @var DefaultSourceProviderInterface $defaultSourceProvider */
        $defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
        $this->defaultSourceCode = $defaultSourceProvider->getCode();

        $this->orderFactory = Bootstrap::getObjectManager()->create(OrderInterfaceFactory::class);
        $this->orderItemFactory = Bootstrap::getObjectManager()->create(OrderItemInterfaceFactory::class);
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
            'SKU-3' => 1
        ]);

        $algorithmResult = $this->shippingAlgorithm->execute($order);

        $sourceSelections = $algorithmResult->getSourceSelections();
        self::assertCount(1, $sourceSelections);

        self::assertArrayHasKey(0, $sourceSelections);

        $defaultSourceSelection = $sourceSelections[0];

        self::assertEquals($this->defaultSourceCode, $defaultSourceSelection->getSourceCode());

        $sourceItemSelections = $defaultSourceSelection->getSourceItemSelections();

        self::assertCount(3, $sourceItemSelections);

        self::assertArrayHasKey(0, $sourceItemSelections);
        self::assertEquals('SKU-1', $sourceItemSelections[0]->getSku());
        self::assertEquals(1, $sourceItemSelections[0]->getQty());
        self::assertEquals(5.5, $sourceItemSelections[0]->getQtyAvailable());

        self::assertArrayHasKey(1, $sourceItemSelections);
        self::assertEquals('SKU-2', $sourceItemSelections[1]->getSku());
        self::assertEquals(1, $sourceItemSelections[1]->getQty());
        self::assertEquals(5, $sourceItemSelections[1]->getQtyAvailable());

        self::assertArrayHasKey(1, $sourceItemSelections);
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
        $order = $this->createOrder([
            'SKU-1' => 27.5,
            'SKU-2' => 4.5,
            'SKU-3' => 3
        ]);

        $algorithmResult = $this->shippingAlgorithm->execute($order);

        $sourceSelections = $algorithmResult->getSourceSelections();

        $expectedResult = [
            [
                'sourceCode' => 'eu-1',
                'selections' => [
                    ['SKU-1', 5.5, 5.5]
                ]
            ],
            [
                'sourceCode' => 'eu-2',
                'selections' => [
                    ['SKU-1', 3, 3],
                    ['SKU-3', 3, 6]
                ]
            ],
            [
                'sourceCode' => 'eu-3',
                'selections' => [
                    ['SKU-1', 10, 10]
                ]
            ],
            [
                'sourceCode' => 'eu-disabled',
                'selections' => [
                    ['SKU-1', 9, 10]
                ]
            ],
            [
                'sourceCode' => 'us-1',
                'selections' => [
                    ['SKU-2', 4.5, 5]
                ]
            ]
        ];

        self::assertCount(count($expectedResult), $sourceSelections);

        foreach ($expectedResult as $idx => $selectionResult) {
            $sourceSelection = $sourceSelections[$idx];

            self::assertEquals($selectionResult['sourceCode'], $sourceSelection->getSourceCode());

            $itemSelections = $selectionResult['selections'];

            $sourceItemSelections = $sourceSelection->getSourceItemSelections();
            self::assertCount(count($itemSelections), $sourceItemSelections);

            foreach ($itemSelections as $itemIdx => $itemResult) {
                $itemSelection = $sourceItemSelections[$itemIdx];
                self::assertEquals($itemResult[0], $itemSelection->getSku());
                self::assertEquals($itemResult[1], $itemSelection->getQty());
                self::assertEquals($itemResult[2], $itemSelection->getQtyAvailable());
            }

        }
    }

    /**
     * Returns order object with specified products
     *
     * @param array $productQtys
     * @return OrderInterface
     */
    private function createOrder(array $productQtys): OrderInterface
    {
        /** @var OrderInterface $order */
        $order = Bootstrap::getObjectManager()->create(OrderInterface::class);

        $orderItems = [];

        foreach ($productQtys as $sku => $qty) {
            $orderItem = $this->orderItemFactory->create();
            $orderItem->setQtyOrdered($qty);
            $orderItem->setSku($sku);
            $orderItem->setDeleted(false);

            $orderItems[] = $orderItem;
        }

        $order->setItems($orderItems);

        return $order;
    }
}
