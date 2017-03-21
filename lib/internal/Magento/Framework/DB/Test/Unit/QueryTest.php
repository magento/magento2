<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

/**
 * Class QueryTest
 */
class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\Api\CriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteriaMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Zend_Db_Statement_Pdo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStmtMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->selectMock = $this->getMock(
            \Magento\Framework\DB\Select::class,
            ['reset', 'columns', 'getConnection'],
            [],
            '',
            false
        );
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
        $this->fetchStmtMock = $this->getMock(
            \Zend_Db_Statement_Pdo::class,
            ['fetch'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
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
        $adapterMock = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['fetchCol'], [], '', false);
        $this->resourceMock->expects($this->once())
            ->method('getIdFieldName')
            ->will($this->returnValue('return-value'));
        $this->selectMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($adapterMock));
        $adapterMock->expects($this->once())
            ->method('fetchCol')
            ->will($this->returnValue('fetch-result'));

        $this->assertEquals('fetch-result', $this->query->getAllIds());
    }

    /**
     * Run test getSize method
     *
     * @return void
     */
    public function testGetSize()
    {
        $adapterMock = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['fetchOne'], [], '', false);

        $this->selectMock->expects($this->once())
            ->method('columns')
            ->with('COUNT(*)');
        $this->selectMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($adapterMock));
        $adapterMock->expects($this->once())
            ->method('fetchOne')
            ->will($this->returnValue(10.689));

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
            ->will($this->returnValue('return-value'));

        $this->assertEquals('return-value', $this->query->fetchAll());
    }

    /**
     * Run test fetchItem method
     *
     * @return void
     */
    public function testFetchItem()
    {
        $adapterMock = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['query'], [], '', false);
        $this->selectMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($adapterMock));
        $adapterMock->expects($this->once())
            ->method('query')
            ->will($this->returnValue($this->fetchStmtMock));
        $this->fetchStmtMock->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(null));

        $this->assertEquals([], $this->query->fetchItem());
    }
}
