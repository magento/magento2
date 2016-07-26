<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin\Product;

use \Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Product\Action;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Action
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var Action
     */
    protected $model;

    protected function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Catalog\Model\Product\Action', [], [], '', false);

        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Indexer\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->getMock(
            'Magento\Framework\Indexer\IndexerRegistry',
            ['get'],
            [],
            '',
            false
        );

        $this->model = new Action($this->indexerRegistryMock);
    }

    public function testAfterUpdateAttributesNonScheduled()
    {
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(false));
        $this->indexerMock->expects($this->once())->method('reindexList')->with([1, 2, 3]);
        $this->prepareIndexer();

        $this->assertEquals(
            $this->subjectMock,
            $this->model->afterUpdateAttributes($this->subjectMock, $this->subjectMock, [1, 2, 3])
        );
    }

    public function testAfterUpdateAttributesScheduled()
    {
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(true));
        $this->indexerMock->expects($this->never())->method('reindexList');
        $this->prepareIndexer();

        $this->assertEquals(
            $this->subjectMock,
            $this->model->afterUpdateAttributes($this->subjectMock, $this->subjectMock, [1, 2, 3])
        );
    }

    public function testAfterUpdateWebsitesNonScheduled()
    {
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(false));
        $this->indexerMock->expects($this->once())->method('reindexList')->with([1, 2, 3]);
        $this->prepareIndexer();

        $this->model->afterUpdateWebsites($this->subjectMock, $this->subjectMock, [1, 2, 3]);
    }

    public function testAfterUpdateWebsitesScheduled()
    {
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(true));
        $this->indexerMock->expects($this->never())->method('reindexList');
        $this->prepareIndexer();

        $this->model->afterUpdateWebsites($this->subjectMock, $this->subjectMock, [1, 2, 3]);
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }
}
