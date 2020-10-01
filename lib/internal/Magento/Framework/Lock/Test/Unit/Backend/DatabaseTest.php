<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit\Backend;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Lock\Backend\Database;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @inheritdoc
 */
class DatabaseTest extends TestCase
{
    /**
     * @var MockObject|ResourceConnection
     */
    private $resource;

    /**
     * @var MockObject|AdapterInterface
     */
    private $connection;

    /**
     * @var MockObject|\Zend_Db_Statement_Interface
     */
    private $statement;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
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

        $this->objectManager = new ObjectManager($this);
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Database $database */
        $this->database = $this->objectManager->getObject(
            Database::class,
            [
                'resource' => $this->resource,
                'deploymentConfig' => $this->deploymentConfig,
            ]
        );
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    public function testLock()
    {
        $this->deploymentConfig
            ->method('isDbAvailable')
            ->with()
            ->willReturn(true);
        $this->statement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(true);

        $this->assertTrue($this->database->lock('testLock'));
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    public function testlockWithTooLongName()
    {
        $this->deploymentConfig
            ->method('isDbAvailable')
            ->with()
            ->willReturn(true);
        $this->statement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(true);

        $this->assertTrue($this->database->lock('BbXbyf9rIY5xuAVdviQJmh76FyoeeVHTDpcjmcImNtgpO4Hnz4xk76ZGEyYALvrQu'));
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    public function testlockWithAlreadyAcquiredLockInSameSession()
    {
        $this->deploymentConfig
            ->method('isDbAvailable')
            ->with()
            ->willReturn(true);
        $this->statement->expects($this->any())
            ->method('fetchColumn')
            ->willReturn(true);

        $this->assertTrue($this->database->lock('testLock'));
        $this->assertTrue($this->database->lock('differentLock'));
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    public function testLockWithUnavailableDeploymentConfig()
    {
        $this->deploymentConfig
            ->expects($this->atLeast(1))
            ->method('isDbAvailable')
            ->with()
            ->willReturn(false);
        $this->assertTrue($this->database->lock('testLock'));
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    public function testUnlockWithUnavailableDeploymentConfig()
    {
        $this->deploymentConfig
            ->expects($this->atLeast(1))
            ->method('isDbAvailable')
            ->with()
            ->willReturn(false);
        $this->assertTrue($this->database->unlock('testLock'));
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    public function testIsLockedWithUnavailableDB()
    {
        $this->deploymentConfig
            ->expects($this->atLeast(1))
            ->method('isDbAvailable')
            ->with()
            ->willReturn(false);
        $this->assertFalse($this->database->isLocked('testLock'));
    }
}
