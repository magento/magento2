<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb as CollectionAbstractDb;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Mview\View\Collection as MviewCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter.
 */
class FulltextFilterTest extends TestCase
{
    /**
     * @var FulltextFilter
     */
    private $fulltextFilter;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var CollectionAbstractDb|MockObject
     */
    private $collectionAbstractDbMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->connectionMock = $this->createPartialMock(Mysql::class, ['select', 'getIndexList']);
        $this->selectMock = $this->createPartialMock(Select::class, ['getPart', 'where']);

        $this->collectionAbstractDbMock = $this->getMockBuilder(CollectionAbstractDb::class)
            ->setMethods(['getConnection', 'getSelect', 'getMainTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fulltextFilter = new FulltextFilter();
    }

    /**
     * Test apply filter
     *
     * @return void
     */
    public function testApply(): void
    {
        $filter = new Filter();
        $filter->setValue('test');

        $this->collectionAbstractDbMock->expects($this->once())
            ->method('getMainTable')
            ->willReturn('testTable');

        $this->collectionAbstractDbMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->once())
            ->method('getIndexList')
            ->willReturn([['INDEX_TYPE' => 'FULLTEXT', 'COLUMNS_LIST' => ['col1', 'col2']]]);

        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->willReturn([]);
        $this->selectMock->expects($this->once())
            ->method('where')
            ->willReturn(null);

        $this->collectionAbstractDbMock->expects($this->exactly(2))
            ->method('getSelect')
            ->willReturn($this->selectMock);

        $this->fulltextFilter->apply($this->collectionAbstractDbMock, $filter);
    }

    /**
     * Apply filter with value in double quotes
     *
     * @return void
     */
    public function testApplyFilter(): void
    {
        $filter = new Filter();
        $filter->setValue('"test"');
        $columns = ['col1', 'col2'];
        $whereCondition = 'MATCH(' . implode(',', $columns) . ') AGAINST(?)';

        $this->collectionAbstractDbMock->expects($this->once())
            ->method('getMainTable')
            ->willReturn('testTable');

        $this->collectionAbstractDbMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->once())
            ->method('getIndexList')
            ->willReturn([['INDEX_TYPE' => 'FULLTEXT', 'COLUMNS_LIST' => $columns]]);

        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->willReturn([]);
        $this->selectMock->expects($this->once())
            ->method('where')
            ->willReturn(null);

        $this->collectionAbstractDbMock->expects($this->exactly(2))
            ->method('getSelect')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with($whereCondition, '"test"');

        $this->fulltextFilter->apply($this->collectionAbstractDbMock, $filter);
    }

    /**
     * Apply with wrong collection type
     *
     * @return void
     */
    public function testApplyWrongCollectionType(): void
    {
        /** @var MviewCollection $mviewCollection */
        $mviewCollection = $this->getMockBuilder(MviewCollection::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException(\InvalidArgumentException::class);
        $this->fulltextFilter->apply($mviewCollection, new Filter());
    }
}
