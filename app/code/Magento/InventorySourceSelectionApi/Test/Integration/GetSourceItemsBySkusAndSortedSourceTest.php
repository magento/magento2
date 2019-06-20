<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Test\Integration;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventorySourceSelectionApi\Model\GetInStockSourceItemsBySkusAndSortedSource;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetSourceItemsBySkusAndSortedSourceTest extends TestCase
{
    /**
     * @var GetInStockSourceItemsBySkusAndSortedSource
     */
    private $subject;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = Bootstrap::getObjectManager()->get(GetInStockSourceItemsBySkusAndSortedSource::class);
    }

    /**
     * @return array
     */
    public function shouldReturnSortedSourceItemsDataProvider(): array
    {
        return [
            [
                ['SKU-1', 'SKU-2', 'SKU-3'],
                ['eu-1', 'eu-2', 'eu-3'],
                [
                    'eu-1/SKU-1' => 5.5,
                    'eu-2/SKU-1' => 3.0,
                ]
            ],
            [
                ['SKU-1', 'SKU-2', 'SKU-3'],
                ['eu-3', 'eu-2', 'eu-1'],
                [
                    'eu-2/SKU-1' => 3.0,
                    'eu-1/SKU-1' => 5.5,
                ]
            ],
            [
                ['SKU-1', 'SKU-2', 'SKU-3', 'SKU-6'],
                ['eu-3', 'eu-2', 'eu-1'],
                [
                    'eu-2/SKU-1' => 3.0,
                    'eu-1/SKU-1' => 5.5,
                    'eu-1/SKU-6' => 0.0
                ]
            ]
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @dataProvider shouldReturnSortedSourceItemsDataProvider
     * @param array $skus
     * @param array $sortedSourceCodes
     * @param array $expected
     */
    public function testShouldReturnSortedSourceItems(array $skus, array $sortedSourceCodes, array $expected): void
    {
        $sourceItems = $this->subject->execute($skus, $sortedSourceCodes);

        self::assertCount(count($expected), $sourceItems);

        $keys = [];
        foreach ($sourceItems as $sourceItem) {
            $key = $sourceItem->getSourceCode() . '/' . $sourceItem->getSku();
            $keys[] = $key;
            self::assertSame($expected[$key], $sourceItem->getQuantity());
            self::assertSame(SourceItemInterface::STATUS_IN_STOCK, $sourceItem->getStatus());
        }

        self::assertSame(array_keys($expected), $keys, 'Sources sorting is not preserved');
    }
}
