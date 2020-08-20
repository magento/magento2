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
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as ResourceModelAbstractDb;
use Magento\Framework\Mview\View\Collection as MviewCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FulltextFilterTest extends TestCase
{
    /**
     * @var FulltextFilter
     */
    private $fulltextFilter;

    /**
     * @var EntityFactoryInterface|MockObject
     */
    private $entityFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    private $fetchStrategyMock;

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
     * @var ResourceModelAbstractDb|MockObject
     */
    private $resourceModelAbstractDb;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock = $this->getMockForAbstractClass(FetchStrategyInterface::class);
        $this->resourceModelAbstractDb = $this->getMockForAbstractClass(FetchStrategyInterface::class);
        $this->connectionMock = $this->createPartialMock(Mysql::class, ['select', 'getIndexList']);
        $this->selectMock = $this->createPartialMock(Select::class, ['getPart', 'where']);

        $this->collectionAbstractDbMock = $this->getMockBuilder(CollectionAbstractDb::class)
            ->addMethods(['getMainTable'])
            ->onlyMethods(['getConnection', 'getSelect'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fulltextFilter = new FulltextFilter();
    }

    /**
     * Test apply filter
     *
     * @param string $searchable
     * @param string $expectedSearchable
     * @dataProvider searchValuesDataProvider
     *
     * @return void
     */
    public function testApply(string $searchable, string $expectedSearchable): void
    {
        $filter = new Filter();
        $filter->setValue($searchable);
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
            ->with($whereCondition, $expectedSearchable);

        $this->fulltextFilter->apply($this->collectionAbstractDbMock, $filter);
    }

    /**
     * Data provider for testApply()
     *
     * @return array
     */
    public function searchValuesDataProvider(): array
    {
        return [
            ['text', 'text'],
            ['"text"', '"text"'],
            ['"text@', '"text\_'],
            ['user_1+test@example.com', 'user_1 test\_example\_com'],
            ['"user_1+test@example.com"', '"user_1+test@example.com"'],
        ];
    }

    /**
     * Apply with wrong collection type
     *
     * @return void
     */
    public function testApplyWrongCollectionType(): void
    {
        $this->expectException('InvalidArgumentException');
        /** @var MviewCollection $mviewCollection */
        $mviewCollection = $this->getMockBuilder(MviewCollection::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException(\InvalidArgumentException::class);
        $this->fulltextFilter->apply($mviewCollection, new Filter());
    }
}
