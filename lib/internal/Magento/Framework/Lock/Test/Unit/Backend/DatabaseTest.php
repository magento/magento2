<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit\Backend;

use Magento\Framework\Lock\Backend\Database;

class DatabaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend_Db_Statement_Interface
     */
    private $statement;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var Database $database
     */
    private $database;

    protected function setUp()
    {
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statement = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection->expects($this->any())
            ->method('query')
            ->willReturn($this->statement);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        /** @var Database $database */
        $this->database = $this->objectManager->getObject(
            Database::class,
            ['resource' => $this->resource]
        );
    }

    public function testLock()
    {
        $this->statement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(true);

        $this->assertTrue($this->database->lock('testLock'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testlockWithTooLongName()
    {
        $this->database->lock('BbXbyf9rIY5xuAVdviQJmh76FyoeeVHTDpcjmcImNtgpO4Hnz4xk76ZGEyYALvrQu');
    }

    /**
     * @expectedException \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testlockWithAlreadyAcquiredLockInSameSession()
    {
        $this->statement->expects($this->any())
            ->method('fetchColumn')
            ->willReturn(true);

        $this->database->lock('testLock');
        $this->database->lock('differentLock');
    }
}
