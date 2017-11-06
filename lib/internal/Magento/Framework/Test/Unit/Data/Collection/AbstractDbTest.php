<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\Data\Collection;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface as Logger;

class AbstractDbTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EntityFactoryInterface|MockObject
     */
    private $entityFactory;

    /**
     * @var Logger|MockObject
     */
    protected $logger;

    /**
     * DB connection mock
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    private $fetchStrategy;

    /**
     * Select object mock
     *
     * @var \Magento\Framework\DB\Select|MockObject
     */
    private $selectMock;

    /**
     * Base items collection class
     *
     * @var AbstractDb|MockObject
     */
    private $abstractDb;

    public function setUp()
    {
        $this->entityFactory = $this->createMock(EntityFactoryInterface::class);
        $this->logger = $this->createMock(Logger::class);
        $this->fetchStrategy = $this->createMock(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class);
        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->connectionMock->expects($this->once())->method('select')->willReturn($this->selectMock);

        $this->abstractDb = $this->getMockForAbstractClass(
            AbstractDb::class,
            [
                'entityFactory' => $this->entityFactory,
                'logger' => $this->logger,
                'fetchStrategy' => $this->fetchStrategy,
                'connection' => $this->connectionMock,
            ]
        );
    }

    /**
     * Test that internal method '_renderOrders' added quotes
     *
     * @test
     */
    public function getDataTest()
    {
        $orderDirection = AbstractDb::SORT_ORDER_ASC;
        $orderField = 'field1';
        $orderFieldWithQuote = '`field1`';
        $this->connectionMock->expects($this->once())
            ->method('quoteIdentifier')
            ->with($orderField)
            ->willReturn($orderFieldWithQuote);
        $this->selectMock->expects($this->once())
            ->method('order')
            ->with(new \Zend_Db_Expr($orderFieldWithQuote . ' ' . $orderDirection));

        $this->abstractDb->setOrder($orderField, $orderDirection);

        $data = [['val11'], ['val12'], ['val11'], ['val22']];
        $this->fetchStrategy->expects($this->once())
            ->method('fetchAll')
            ->willReturn($data);

        $this->assertEquals(
            $data,
            $this->abstractDb->getData(),
            'Fetch data is wrong'
        );
    }
}
