<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\ResourceModel\Block\Relation\Store;

use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\ResourceModel\Block\Relation\Store\ReadHandler;
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

        $block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
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
        $this->assertInstanceOf(\Magento\Cms\Model\Block::class, $result);
    }

    public function testExecuteWithNoId()
    {
        $block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
            ->disableOriginalConstructor()
            ->getMock();

        $block->expects($this->once())
            ->method('getId')
            ->willReturn(false);

        $result = $this->model->execute($block);
        $this->assertInstanceOf(\Magento\Cms\Model\Block::class, $result);
    }
}
