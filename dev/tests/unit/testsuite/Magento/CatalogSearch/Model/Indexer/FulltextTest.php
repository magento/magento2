<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

class FulltextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext
     */
    protected $model;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fullMock;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\RowsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowsMock;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerMock;

    /**
     * @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp()
    {
        $this->fullMock = $this->getMock(
            'Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->rowsMock = $this->getMock(
            'Magento\CatalogSearch\Model\Indexer\Fulltext\Action\RowsFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'load', 'isInvalid', 'isWorking', '__wakeup']
        );

        $this->indexerRegistryMock = $this->getMock('Magento\Indexer\Model\IndexerRegistry', ['get'], [], '', false);

        $this->model = new \Magento\CatalogSearch\Model\Indexer\Fulltext(
            $this->fullMock,
            $this->rowsMock,
            $this->indexerRegistryMock
        );
    }

    public function testExecuteWithIndexer()
    {
        $ids = [1, 2, 3];

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));

        $rowMock = $this->getMock(
            'Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Rows',
            ['reindex'],
            [],
            '',
            false
        );
        $rowMock->expects($this->once())->method('reindex')->with($ids)->will($this->returnSelf());

        $this->rowsMock->expects($this->once())->method('create')->will($this->returnValue($rowMock));

        $this->model->execute($ids);
    }
}
