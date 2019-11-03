<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\GetBlockByIdentifier;

/**
 * Test for Magento\Cms\Model\GetBlockByIdentifier
 */

class GetBlockByIdentifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetBlockByIdentifier
     */
    private $getBlockByIdentifierCommand;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\Block
     */
    private $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\ResourceModel\Block
     */
    private $blockResource;

    protected function setUp()
    {
        $this->blockFactory = $this->getMockBuilder(\Magento\Cms\Model\BlockFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->blockResource = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Block::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'getId'])
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
