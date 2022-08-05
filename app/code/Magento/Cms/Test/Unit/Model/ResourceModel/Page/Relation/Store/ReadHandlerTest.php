<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\ResourceModel\Page\Relation\Store;

use Magento\Cms\Model\ResourceModel\Page;
use Magento\Cms\Model\ResourceModel\Page\Relation\Store\ReadHandler;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReadHandlerTest extends TestCase
{
    /**
     * @var ReadHandler
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

        $this->model = new ReadHandler(
            $this->metadataPool,
            $this->resourcePage
        );
    }

    public function testExecute()
    {
        $entityId = 1;
        $storeId = 1;

        $this->resourcePage->expects($this->once())
            ->method('lookupStoreIds')
            ->willReturn([$storeId]);

        $page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $page->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($entityId);
        $page->expects($this->once())
            ->method('setData')
            ->with('store_id', [$storeId])
            ->willReturnSelf();

        $result = $this->model->execute($page);
        $this->assertInstanceOf(\Magento\Cms\Model\Page::class, $result);
    }

    public function testExecuteWithNoId()
    {
        $page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $page->expects($this->once())
            ->method('getId')
            ->willReturn(false);

        $result = $this->model->execute($page);
        $this->assertInstanceOf(\Magento\Cms\Model\Page::class, $result);
    }
}
