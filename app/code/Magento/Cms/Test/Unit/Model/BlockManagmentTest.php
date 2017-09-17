<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\BlockManagment;

/**
 * Test for Magento\Cms\Model\BlockManagment
 */

class BlockManagmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BlockManagment
     */
    protected $blockManagment;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\Block
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\ResourceModel\Block
     */
    protected $blockResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Api\Data\StoreInterface
     */
    protected $store;

    public function setUp()
    {
        $this->blockFactory = $this->getMockBuilder(\Magento\Cms\Model\BlockFactory::class)
            ->disableOriginalConstructor(true)
            ->setMethods(['create'])
            ->getMock();

        $this->blockResource = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Block::class)
            ->disableOriginalConstructor(true)
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor(true)
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor(true)
            ->getMock();

        $this->block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'getId'])
            ->getMock();

        $this->blockManagment = new BlockManagment($this->blockFactory, $this->blockResource, $this->storeManager);
    }

    /**
     * Test for getByIdentifier method
     */
    public function testGetByIdentifier()
    {
        $identifier = 'banner';
        $storeId = null;

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);

        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn(1);

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

        $this->blockManagment->getByIdentifier($identifier, $storeId);
    }
}