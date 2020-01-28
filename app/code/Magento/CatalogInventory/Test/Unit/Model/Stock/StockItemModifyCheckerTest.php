<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Stock;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Stock\StockItemModifyChecker;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for StockItemModifyChecker.
 * @see StockItemModifyChecker
 */
class StockItemModifyCheckerTest extends TestCase
{
    /**
     * @var StockItemModifyChecker
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
     * @var ArrayUtils|MockObject
     */
    private $arrayUtils;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->stockItemRepository = $this->createPartialMock(StockItemRepository::class, ['get']);
        $this->arrayUtils = $this->createPartialMock(ArrayUtils::class, ['recursiveDiff']);
        $this->stockItemModel = $this->createPartialMock(StockItem::class, ['getId', 'getData']);

        $this->model = $objectManager->getObject(
            StockItemModifyChecker::class,
            [
                'stockItemRepository' => $this->stockItemRepository,
                'arrayUtils' => $this->arrayUtils,
                'skippedAttributes' => [StockItemInterface::LOW_STOCK_DATE],
            ]
        );
    }

    /**
     * Test for IsModified method when data is not modified.
     *
     * @return void
     */
    public function testIsModifiedForNotModifiedModel(): void
    {
        $itemFromRepository = [
            'id' => 1,
            'low_stock_date' => '01.01.2020',
            'qty' => 100,
        ];
        $model = [
            'id' => 1,
            'low_stock_date' => '01.01.2021',
            'qty' => 100
        ];
        $this->stockItemModel->expects($this->exactly(2))->method('getId')->willReturn($model['id']);
        $this->stockItemRepository->expects($this->once())->method('get')->willReturn($this->stockItemModel);
        $this->stockItemModel->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls($itemFromRepository, $model);
        $this->arrayUtils->expects($this->once())->method('recursiveDiff')->willReturn([]);

        $this->assertFalse($this->model->isModified($this->stockItemModel));
    }

    /**
     * Test for IsModified method when model is new.
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
     * Test for IsModified method when found difference between data.
     *
     * @return void
     */
    public function testIsModifiedWhenDifferenceFound(): void
    {
        $itemFromRepository = [
            'id' => 1,
            'low_stock_date' => '01.01.2020',
            'qty' => 100,
        ];
        $model = [
            'id' => 1,
            'low_stock_date' => '01.01.2021',
            'qty' => 99
        ];
        $this->stockItemModel->expects($this->exactly(2))->method('getId')->willReturn($model['id']);
        $this->stockItemRepository->expects($this->once())->method('get')->willReturn($this->stockItemModel);
        $this->stockItemModel->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls($itemFromRepository, $model);
        $this->arrayUtils->expects($this->once())->method('recursiveDiff')->willReturn(['qty' => 100]);

        $this->assertTrue($this->model->isModified($this->stockItemModel));
    }
}
