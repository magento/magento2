<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\AbstractIndexer;

class AbstractIndexerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexBuilder;

    /**
     * @var AbstractIndexer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexer;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventManagerMock;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->_eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->indexBuilder = $this->createMock(\Magento\CatalogRule\Model\Indexer\IndexBuilder::class);

        $this->indexer = $this->getMockForAbstractClass(
            AbstractIndexer::class,
            [
                $this->indexBuilder,
                $this->_eventManagerMock
            ]
        );
        $cacheMock = $this->createMock(\Magento\Framework\App\CacheInterface::class);
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
     *
     * @return void
     */
    public function testExecuteListWithEmptyIds()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Could not rebuild index for empty products array');

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
     *
     * @return void
     */
    public function testExecuteRowWithEmptyId()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('We can\'t rebuild the index for an undefined product.');

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
