<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\ResourceModel\Page\Relation\Store;

use Magento\Cms\Model\ResourceModel\Page;
use Magento\Cms\Model\ResourceModel\Page\Relation\Store\SaveHandler;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Cms\Api\Data\PageInterface;

class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaveHandler
     */
    protected $model;

    /**
     * @var MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $metadataPool;

    /**
     * @var Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourcePage;

    protected function setUp(): void
    {
        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourcePage = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SaveHandler(
            $this->metadataPool,
            $this->resourcePage
        );
    }

    public function testExecute()
    {
        $entityId = 1;
        $linkId = 2;
        $oldStore = 1;
        $newStore = 2;
        $linkField = 'link_id';

        $adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMockForAbstractClass();

        $whereForDelete = [
            $linkField . ' = ?' => $linkId,
            'store_id IN (?)' => [$oldStore],
        ];
        $adapter->expects($this->once())
            ->method('delete')
            ->with('cms_page_store', $whereForDelete)
            ->willReturnSelf();

        $whereForInsert = [
            $linkField => $linkId,
            'store_id' => $newStore,
        ];
        $adapter->expects($this->once())
            ->method('insertMultiple')
            ->with('cms_page_store', [$whereForInsert])
            ->willReturnSelf();

        $entityMetadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
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
            ->with(PageInterface::class)
            ->willReturn($entityMetadata);

        $this->resourcePage->expects($this->once())
            ->method('lookupStoreIds')
            ->willReturn([$oldStore]);
        $this->resourcePage->expects($this->once())
            ->method('getTable')
            ->with('cms_page_store')
            ->willReturn('cms_page_store');

        $page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStores',
                'getStoreId',
                'getId',
                'getData',
            ])
            ->getMock();
        $page->expects($this->once())
            ->method('getStores')
            ->willReturn(null);
        $page->expects($this->once())
            ->method('getStoreId')
            ->willReturn($newStore);
        $page->expects($this->once())
            ->method('getId')
            ->willReturn($entityId);
        $page->expects($this->exactly(2))
            ->method('getData')
            ->with($linkField)
            ->willReturn($linkId);

        $result = $this->model->execute($page);
        $this->assertInstanceOf(PageInterface::class, $result);
    }
}
