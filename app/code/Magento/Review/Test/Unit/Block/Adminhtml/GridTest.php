<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Review\Test\Unit\Block\Adminhtml;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Review\Block\Adminhtml\Grid;
use Magento\Review\Helper\Action\Pager;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Grid|MockObject
     */
    private $grid;

    /**
     * @var Pager|MockObject
     */
    private $pager;

    /**
     * @var Collection|MockObject
     */
    private $collection;

    protected function setUp(): void
    {
        $this->pager = $this->createMock(Pager::class);
        $this->collection = $this->createMock(Collection::class);
        $this->grid = $this->getMockBuilder(Grid::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollection'])
            ->getMock();
        $this->grid->method('getCollection')->willReturn($this->collection);
        $this->setPropertyValue($this->grid, '_reviewActionPager', $this->pager);
    }

    #[DataProvider('sizeWithinLimitDataProvider')]
    public function testStoresResultingIdsWhenSizeIsWithinLimit(int $size): void
    {
        $ids = ['7', '3', '5'];
        $this->collection->method('getSize')->willReturn($size);
        $this->collection->expects($this->once())->method('getResultingIds')->willReturn($ids);
        $this->pager->expects($this->once())->method('setStorageId')->with('reviews');
        $this->pager->expects($this->once())->method('setItems')->with($ids);

        $this->assertSame($this->grid, $this->invokeAfterLoadCollection());
    }

    public static function sizeWithinLimitDataProvider(): array
    {
        return [
            'empty grid' => [0],
            'small grid' => [3],
            'exactly at limit' => [1000],
        ];
    }

    public function testStoresEmptyListWithoutFetchingIdsWhenSizeExceedsLimit(): void
    {
        $this->collection->method('getSize')->willReturn(1001);
        $this->collection->expects($this->never())->method('getResultingIds');
        $this->pager->expects($this->once())->method('setStorageId')->with('reviews');
        $this->pager->expects($this->once())->method('setItems')->with([]);

        $this->assertSame($this->grid, $this->invokeAfterLoadCollection());
    }

    private function invokeAfterLoadCollection(): mixed
    {
        return (new \ReflectionMethod(Grid::class, '_afterLoadCollection'))->invoke($this->grid);
    }
}
