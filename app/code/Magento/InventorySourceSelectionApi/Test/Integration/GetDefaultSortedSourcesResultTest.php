<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Test\Integration;

use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Model\Algorithms\Result\GetDefaultSortedSourcesResult;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class GetDefaultSortedSourcesResultTest extends TestCase
{
    /**
     * @var GetDefaultSortedSourcesResult
     */
    private $subject;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->subject = Bootstrap::getObjectManager()->get(GetDefaultSortedSourcesResult::class);
        $this->inventoryRequestFactory = Bootstrap::getObjectManager()->get(InventoryRequestInterfaceFactory::class);
        $this->itemRequestFactory = Bootstrap::getObjectManager()->get(ItemRequestInterfaceFactory::class);
        $this->sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);
    }

    /**
     * @return array
     */
    public function shouldReturnDefaultResultsDataProvider(): array
    {
        return [
            [
                10,
                [
                    ['sku' => 'SKU-1', 'qty' => 7],
                ],
                [
                    'eu-1',
                    'eu-2',
                    'eu-3',
                ],
                [
                    'eu-1/SKU-1' => ['deduct' => 5.5, 'avail' => 5.5],
                    'eu-2/SKU-1' => ['deduct' => 1.5, 'avail' => 3],
                ],
                true
            ],
            [
                10,
                [
                    ['sku' => 'SKU-1', 'qty' => 15],
                ],
                [
                    'eu-1',
                    'eu-2',
                    'eu-3',
                ],
                [
                    'eu-1/SKU-1' => ['deduct' => 5.5, 'avail' => 5.5],
                    'eu-2/SKU-1' => ['deduct' => 3, 'avail' => 3],
                ],
                false
            ],
            [
                10,
                [
                    ['sku' => 'SKU-1', 'qty' => 5],
                    ['sku' => 'SKU-2', 'qty' => 3],
                ],
                [
                    'eu-1',
                    'eu-2',
                    'eu-3',
                ],
                [
                    'eu-1/SKU-1' => ['deduct' => 5, 'avail' => 5.5],
                    'eu-2/SKU-1' => ['deduct' => 0, 'avail' => 3],
                ],
                false
            ],
            [
                10,
                [
                    ['sku' => 'SKU-1', 'qty' => 5],
                    ['sku' => 'SKU-2', 'qty' => 3],
                ],
                [
                    'eu-3',
                    'eu-2',
                    'eu-1',
                ],
                [
                    'eu-1/SKU-1' => ['deduct' => 2, 'avail' => 5.5],
                    'eu-2/SKU-1' => ['deduct' => 3, 'avail' => 3],
                ],
                false
            ],
            [
                20,
                [
                    ['sku' => 'SKU-2', 'qty' => 3],
                ],
                [
                    'us-1',
                ],
                [
                    'us-1/SKU-2' => ['deduct' => 3, 'avail' => 5],
                ],
                true
            ],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @dataProvider shouldReturnDefaultResultsDataProvider
     * @param int $stockId
     * @param array $requestItemsData
     * @param array $sortedSourcesCodes
     * @param array $expected
     * @param bool $expectIsShippable
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testShouldReturnDefaultResults(
        int $stockId,
        array $requestItemsData,
        array $sortedSourcesCodes,
        array $expected,
        bool $expectIsShippable
    ): void {
        $requestItems = [];
        foreach ($requestItemsData as $requestItemData) {
            $requestItems[] = $this->itemRequestFactory->create($requestItemData);
        }

        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items'   => $requestItems
        ]);

        $sortedSources = [];
        foreach ($sortedSourcesCodes as $sortedSourceCode) {
            $sortedSources[] = $this->sourceRepository->get($sortedSourceCode);
        }

        $res = $this->subject->execute(
            $inventoryRequest,
            $sortedSources
        );

        $sourceSelectionItems = $res->getSourceSelectionItems();
        self::assertCount(count($expected), $sourceSelectionItems);
        self::assertSame($expectIsShippable, $res->isShippable());

        foreach ($sourceSelectionItems as $selectionItem) {
            $key = $selectionItem->getSourceCode() . '/' . $selectionItem->getSku();
            self::assertSame((float) $expected[$key]['deduct'], $selectionItem->getQtyToDeduct());
            self::assertSame((float) $expected[$key]['avail'], $selectionItem->getQtyAvailable());
        }
    }
}
