<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\AbstractIndexer;

class AbstractIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexBuilder;

    /**
     * @var AbstractIndexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexer;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_eventManagerMock = $this->getMock('\Magento\Framework\Event\ManagerInterface');
        $this->indexBuilder = $this->getMock('Magento\CatalogRule\Model\Indexer\IndexBuilder', [], [], '', false);

        $this->indexer = $this->getMockForAbstractClass(
            AbstractIndexer::class,
            [
                $this->indexBuilder,
                $this->_eventManagerMock
            ]
        );
        $cacheMock = $this->getMock(\Magento\Framework\App\CacheInterface::class);
        $reflection = new \ReflectionClass(AbstractIndexer::class);
        $reflectionProperty = $reflection->getProperty('cacheManager');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->indexer, $cacheMock);
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute()
    {
        $ids = [1, 2, 5];
        $this->indexer->expects($this->once())->method('doExecuteList')->with($ids);

        $this->indexer->execute($ids);
    }

    /**
     * Test execute full reindex action
     *
     * @return void
     */
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not rebuild index for empty products array
     *
     * @return void
     */
    public function testExecuteListWithEmptyIds()
    {
        $this->indexer->executeList([]);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    public function testExecuteList()
    {
        $ids = [1, 2, 5];
        $this->indexer->expects($this->once())->method('doExecuteList')->with($ids);

        $this->indexer->executeList($ids);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We can't rebuild the index for an undefined product.
     *
     * @return void
     */
    public function testExecuteRowWithEmptyId()
    {
        $this->indexer->executeRow(null);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    public function testExecuteRow()
    {
        $id = 5;
        $this->indexer->expects($this->once())->method('doExecuteRow')->with($id);

        $this->indexer->executeRow($id);
    }
}
