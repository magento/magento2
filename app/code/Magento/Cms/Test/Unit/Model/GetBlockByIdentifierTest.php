<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\GetBlockByIdentifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Cms\Model\GetBlockByIdentifier
 */

class GetBlockByIdentifierTest extends TestCase
{
    /**
     * @var GetBlockByIdentifier
     */
    private $getBlockByIdentifierCommand;

    /**
     * @var MockObject|Block
     */
    private $block;

    /**
     * @var MockObject|BlockFactory
     */
    private $blockFactory;

    /**
     * @var MockObject|\Magento\Cms\Model\ResourceModel\Block
     */
    private $blockResource;

    protected function setUp(): void
    {
        $this->blockFactory = $this->getMockBuilder(BlockFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->blockResource = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Block::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->addMethods(['setStoreId'])
            ->onlyMethods(['getId'])
            ->getMock();

        $this->getBlockByIdentifierCommand = new GetBlockByIdentifier($this->blockFactory, $this->blockResource);
    }

    /**
     * Test for getByIdentifier method
     */
    public function testGetByIdentifier()
    {
        $identifier = 'banner';
        $storeId = 0;

        $this->blockFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->block);

        $this->block->expects($this->once())
            ->method('setStoreId')
            ->willReturn($this->block);

        $this->block->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->blockResource->expects($this->once())
            ->method('load')
            ->with($this->block, $identifier)
            ->willReturn($this->block);

        $this->getBlockByIdentifierCommand->execute($identifier, $storeId);
    }
}
