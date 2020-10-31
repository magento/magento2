<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for full test filter
 */
namespace Magento\Framework\View\Test\Unit\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb as CollectionAbstractDb;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as ResourceModelAbstractDb;
use Magento\Framework\Mview\View\Collection as MviewCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FulltextFilterTest extends TestCase
{
    /**
     * @var FulltextFilter
     */
    protected $fulltextFilter;

    /**
     * @var EntityFactoryInterface|MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * @var CollectionAbstractDb|MockObject
     */
    protected $collectionAbstractDbMock;

    /**
     * @var ResourceModelAbstractDb|MockObject
     */
    protected $resourceModelAbstractDb;

    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock = $this->getMockForAbstractClass(FetchStrategyInterface::class);
        $this->resourceModelAbstractDb = $this->getMockForAbstractClass(FetchStrategyInterface::class);
        $this->connectionMock = $this->createPartialMock(Mysql::class, ['select', 'getIndexList']);
        $this->selectMock = $this->createPartialMock(Select::class, ['getPart', 'where']);

        $this->resourceModelAbstractDb = $this->getMockBuilder(ResourceModelAbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->collectionAbstractDbMock = $this->getMockBuilder(CollectionAbstractDb::class)
            ->setMethods(['getConnection', 'getSelect', 'getMainTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fulltextFilter = new FulltextFilter();
    }

    public function testApply()
    {
        $filter = new Filter();
        $filter->setValue('test');

        $this->collectionAbstractDbMock->expects($this->any())
            ->method('getMainTable')
            ->willReturn('testTable');

        $this->collectionAbstractDbMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
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

    public function testApplyWrongCollectionType()
    {
        $this->expectException('InvalidArgumentException');
        /** @var MviewCollection $mviewCollection */
        $mviewCollection = $this->getMockBuilder(MviewCollection::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->fulltextFilter->apply($mviewCollection, new Filter());
    }
}
