<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Stock;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\CatalogInventory\Model\Stock\StockItemChecker;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for StockItemChecker.
 * @see StockItemChecker
 */
class StockItemCheckerTest extends TestCase
{
    /**
     * @var StockItemChecker
     */
    private $model;

    /**
     * @var StockItemRepository|MockObject
     */
    private $stockItemRepository;

    /**
     * @var StockItem|MockObject
     */
    private $stockItemModel;

    /**
     * @var ArrayUtils
     */
    private $arrayUtils;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->stockItemRepository = $this->createPartialMock(StockItemRepository::class, ['get']);
        $this->arrayUtils = $objectManager->getObject(ArrayUtils::class);
        $this->stockItemModel = $this->createPartialMock(StockItem::class, ['getId', 'getData']);

        $this->model = $objectManager->getObject(
            StockItemChecker::class,
            [
                'stockItemRepository' => $this->stockItemRepository,
                'arrayUtils' => $this->arrayUtils,
                'skippedAttributes' => [StockItemInterface::LOW_STOCK_DATE],
            ]
        );
    }

    /**
     * Test for isModified method when model is new.
     *
     * @return void
     */
    public function testIsModifiedWhenModelIsNew(): void
    {
        $this->stockItemModel->expects($this->once())->method('getId')->willReturn(null);
        $this->stockItemRepository->expects($this->never())->method('get');

        $this->assertTrue($this->model->isModified($this->stockItemModel));
    }

    /**
     * Test for isModified method when found difference between data.
     *
     * @param array $itemFromRepository
     * @param array $model
     * @param bool $expectedResult
     * @return void
     * @dataProvider stockItemModelDataProvider
     */
    public function testIsModified(
        array $itemFromRepository,
        array $model,
        bool $expectedResult
    ): void {
        $this->stockItemModel->expects($this->exactly(2))->method('getId')->willReturn($model['id']);
        $this->stockItemRepository->expects($this->once())->method('get')->willReturn($this->stockItemModel);
        $this->stockItemModel->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls($itemFromRepository, $model);

        $this->assertEquals($expectedResult, $this->model->isModified($this->stockItemModel));
    }

    /**
     * Data provider for testIsModified.
     *
     * @return array
     */
    public function stockItemModelDataProvider(): array
    {
        return [
            'Model is modified' => [
                'stockItemFromRepository' => [
                    'id' => 1,
                    'low_stock_date' => '01.01.2020',
                    'qty' => 100,
                ],
                'model' => [
                    'id' => 1,
                    'low_stock_date' => '01.01.2021',
                    'qty' => 99,
                ],
                'expectedResult' => true,
            ],
            'Model is not modified' => [
                'stockItemFromRepository' => [
                    'id' => 1,
                    'low_stock_date' => '01.01.2020',
                    'qty' => 100,
                ],
                'model' => [
                    'id' => 1,
                    'low_stock_date' => '01.01.2021',
                    'qty' => 100,
                ],
                'expectedResult' => false,
            ],
        ];
    }
}
