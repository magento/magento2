<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\ResourceModel\Page\Relation\Store;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\ResourceModel\Page;
use Magento\Cms\Model\ResourceModel\Page\Relation\Store\SaveHandler;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    /**
     * @var SaveHandler
     */
    protected $model;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPool;

    /**
     * @var Page|MockObject
     */
    protected $resourcePage;

    protected function setUp(): void
    {
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourcePage = $this->getMockBuilder(Page::class)
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

        $adapter = $this->getMockBuilder(AdapterInterface::class)
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

        $entityMetadata = $this->getMockBuilder(EntityMetadata::class)
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
            ->addMethods(['getStoreId'])
            ->onlyMethods([
                'getStores',
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
