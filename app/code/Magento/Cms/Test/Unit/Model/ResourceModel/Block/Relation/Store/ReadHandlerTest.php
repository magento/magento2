<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\ResourceModel\Block\Relation\Store;

use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\ResourceModel\Block\Relation\Store\ReadHandler;
use Magento\Cms\Model\Block as CmsModelBlock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReadHandlerTest extends TestCase
{
    /**
     * @var ReadHandler
     */
    protected $model;

    /**
     * @var Block|MockObject
     */
    protected $resourceBlock;

    protected function setUp(): void
    {
        $this->resourceBlock = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new ReadHandler(
            $this->resourceBlock
        );
    }

    public function testExecute()
    {
        $entityId = 1;
        $storeId = 1;

        $this->resourceBlock->expects($this->once())
            ->method('lookupStoreIds')
            ->willReturn([$storeId]);

        $block = $this->getMockBuilder(CmsModelBlock::class)
            ->disableOriginalConstructor()
            ->getMock();

        $block->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($entityId);
        $block->expects($this->exactly(2))
            ->method('setData')
            ->willReturnMap([
                ['store_id', [$storeId], $block],
                ['stores', [$storeId], $block],
            ]);

        $result = $this->model->execute($block);
        $this->assertInstanceOf(CmsModelBlock::class, $result);
    }

    public function testExecuteWithNoId()
    {
        $block = $this->getMockBuilder(CmsModelBlock::class)
            ->disableOriginalConstructor()
            ->getMock();

        $block->expects($this->once())
            ->method('getId')
            ->willReturn(false);

        $result = $this->model->execute($block);
        $this->assertInstanceOf(CmsModelBlock::class, $result);
    }
}
