<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

/**
 * Class QueryTest
 */
class QueryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\Api\CriteriaInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $criteriaMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Zend_Db_Statement_Pdo|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fetchStmtMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\DB\Query
     */
    protected $query;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->selectMock =
            $this->createPartialMock(\Magento\Framework\DB\Select::class, ['reset', 'columns', 'getConnection']);
        $this->criteriaMock = $this->getMockForAbstractClass(
            \Magento\Framework\Api\CriteriaInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->resourceMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['getIdFieldName']
        );
        $this->fetchStmtMock = $this->createPartialMock(\Zend_Db_Statement_Pdo::class, ['fetch']);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->query = $objectManager->getObject(
            \Magento\Framework\DB\Query::class,
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
        $adapterMock = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['fetchCol']);
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
        $adapterMock = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['fetchOne']);

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
        $adapterMock = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['query']);
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
