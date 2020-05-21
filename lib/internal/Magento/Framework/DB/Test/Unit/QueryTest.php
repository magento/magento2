<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Query;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class QueryTest extends TestCase
{
    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * @var CriteriaInterface|MockObject
     */
    protected $criteriaMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var \Zend_Db_Statement_Pdo|MockObject
     */
    protected $fetchStmtMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var Query
     */
    protected $query;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->selectMock =
            $this->createPartialMock(Select::class, ['reset', 'columns', 'getConnection']);
        $this->criteriaMock = $this->getMockForAbstractClass(
            CriteriaInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->resourceMock = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['getIdFieldName']
        );
        $this->fetchStmtMock = $this->createPartialMock(\Zend_Db_Statement_Pdo::class, ['fetch']);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            FetchStrategyInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->query = $objectManager->getObject(
            Query::class,
            [
                'select' => $this->selectMock,
                'criteria' => $this->criteriaMock,
                'resource' => $this->resourceMock,
                'fetchStrategy' => $this->fetchStrategyMock
            ]
        );
    }

    /**
     * Run test getAllIds method
     *
     * @return void
     */
    public function testGetAllIds()
    {
        $adapterMock = $this->createPartialMock(Mysql::class, ['fetchCol']);
        $this->resourceMock->expects($this->once())
            ->method('getIdFieldName')
            ->willReturn('return-value');
        $this->selectMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($adapterMock);
        $adapterMock->expects($this->once())
            ->method('fetchCol')
            ->willReturn('fetch-result');

        $this->assertEquals('fetch-result', $this->query->getAllIds());
    }

    /**
     * Run test getSize method
     *
     * @return void
     */
    public function testGetSize()
    {
        $adapterMock = $this->createPartialMock(Mysql::class, ['fetchOne']);

        $this->selectMock->expects($this->once())
            ->method('columns')
            ->with('COUNT(*)');
        $this->selectMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($adapterMock);
        $adapterMock->expects($this->once())
            ->method('fetchOne')
            ->willReturn(10.689);

        $this->assertEquals(10, $this->query->getSize());
    }

    /**
     * Run test fetchAll method
     *
     * @return void
     */
    public function testFetchAll()
    {
        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn('return-value');

        $this->assertEquals('return-value', $this->query->fetchAll());
    }

    /**
     * Run test fetchItem method
     *
     * @return void
     */
    public function testFetchItem()
    {
        $adapterMock = $this->createPartialMock(Mysql::class, ['query']);
        $this->selectMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($adapterMock);
        $adapterMock->expects($this->once())
            ->method('query')
            ->willReturn($this->fetchStmtMock);
        $this->fetchStmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(null);

        $this->assertEquals([], $this->query->fetchItem());
    }
}
