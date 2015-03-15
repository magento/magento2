<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

class AbstractIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\AbstractIndexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexer;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    protected function setUp()
    {
        $this->_eventManagerMock = $this->getMock('\Magento\Framework\Event\ManagerInterface');
        $this->indexBuilder = $this->getMock('Magento\CatalogRule\Model\Indexer\IndexBuilder', [], [], '', false);

        $this->indexer = $this->getMockForAbstractClass(
            'Magento\CatalogRule\Model\Indexer\AbstractIndexer',
            [
                $this->indexBuilder,
                $this->_eventManagerMock
            ]
        );
    }

    public function testExecute()
    {
        $ids = [1, 2, 5];
        $this->indexer->expects($this->once())->method('doExecuteList')->with($ids);

        $this->indexer->execute($ids);
    }

    public function testExecuteFull()
    {
        $this->indexBuilder->expects($this->once())->method('reindexFull');
        $this->_eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'clean_cache_by_tags',
                ['object' => $this->indexer]
            );

        $this->indexer->executeFull();
    }

    /**
     * @expectedException \Magento\CatalogRule\CatalogRuleException
     * @expectedExceptionMessage Could not rebuild index for empty products array
     */
    public function testExecuteListWithEmptyIds()
    {
        $this->indexer->executeList([]);
    }

    public function testExecuteList()
    {
        $ids = [1, 2, 5];
        $this->indexer->expects($this->once())->method('doExecuteList')->with($ids);

        $this->indexer->executeList($ids);
    }

    /**
     * @expectedException \Magento\CatalogRule\CatalogRuleException
     * @expectedExceptionMessage Could not rebuild index for undefined product
     */
    public function testExecuteRowWithEmptyId()
    {
        $this->indexer->executeRow(null);
    }

    public function testExecuteRow()
    {
        $id = 5;
        $this->indexer->expects($this->once())->method('doExecuteRow')->with($id);

        $this->indexer->executeRow($id);
    }
}
