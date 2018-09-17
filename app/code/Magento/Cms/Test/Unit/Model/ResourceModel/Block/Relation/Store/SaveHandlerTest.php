<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\ResourceModel\Block\Relation\Store;

use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\ResourceModel\Block\Relation\Store\SaveHandler;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Cms\Api\Data\BlockInterface;

class SaveHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveHandler
     */
    protected $model;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPool;

    /**
     * @var Block|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceBlock;

    protected function setUp()
    {
        $this->metadataPool = $this->getMockBuilder('Magento\Framework\EntityManager\MetadataPool')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceBlock = $this->getMockBuilder('Magento\Cms\Model\ResourceModel\Block')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SaveHandler(
            $this->metadataPool,
            $this->resourceBlock
        );
    }

    public function testExecute()
    {
        $entityId = 1;
        $linkId = 2;
        $oldStore = 1;
        $newStore = 2;
        $linkField = 'link_id';

        $adapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->getMockForAbstractClass();

        $whereForDelete = [
            $linkField . ' = ?' => $linkId,
            'store_id IN (?)' => [$oldStore],
        ];
        $adapter->expects($this->once())
            ->method('delete')
            ->with('cms_block_store', $whereForDelete)
            ->willReturnSelf();

        $whereForInsert = [
            $linkField => $linkId,
            'store_id' => $newStore,
        ];
        $adapter->expects($this->once())
            ->method('insertMultiple')
            ->with('cms_block_store', [$whereForInsert])
            ->willReturnSelf();

        $entityMetadata = $this->getMockBuilder('Magento\Framework\EntityManager\EntityMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $entityMetadata->expects($this->once())
            ->method('getEntityConnection')
            ->willReturn($adapter);
        $entityMetadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn($linkField);

        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(BlockInterface::class)
            ->willReturn($entityMetadata);

        $this->resourceBlock->expects($this->once())
            ->method('lookupStoreIds')
            ->willReturn([$oldStore]);
        $this->resourceBlock->expects($this->once())
            ->method('getTable')
            ->with('cms_block_store')
            ->willReturn('cms_block_store');

        $block = $this->getMockBuilder('Magento\Cms\Model\Block')
            ->disableOriginalConstructor()
            ->setMethods([
                'getStores',
                'getId',
                'getData',
            ])
            ->getMock();
        $block->expects($this->once())
            ->method('getStores')
            ->willReturn($newStore);
        $block->expects($this->once())
            ->method('getId')
            ->willReturn($entityId);
        $block->expects($this->exactly(2))
            ->method('getData')
            ->with($linkField)
            ->willReturn($linkId);

        $result = $this->model->execute($block);
        $this->assertInstanceOf(BlockInterface::class, $result);
    }
}
