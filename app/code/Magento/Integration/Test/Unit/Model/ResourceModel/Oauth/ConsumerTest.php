<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\ResourceModel\Oauth;

use \Magento\Integration\Model\ResourceModel\Oauth\Consumer as ConsumerResourceModel;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Integration\Model\Oauth\Consumer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ConsumerResourceModel
 */
class ConsumerTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var Consumer
     */
    protected $consumerMock;

    /**
     * @var ConsumerResourceModel
     */
    protected $consumerResource;

    protected function setUp(): void
    {
        $this->consumerMock = $this->createPartialMockWithReflection(
            Consumer::class,
            ['setUpdatedAt', 'getId']
        );

        $this->connectionMock = $this->createMock(Mysql::class);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        // Mock ObjectManager to prevent "ObjectManager isn't initialized" error
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        AppObjectManager::setInstance($objectManagerMock);

        $this->consumerResource = new ConsumerResourceModel(
            $contextMock,
            new DateTime()
        );
    }

    public function testAfterDelete(): void
    {
        $this->connectionMock->expects($this->exactly(2))->method('delete');
        $this->assertInstanceOf(
            ConsumerResourceModel::class,
            $this->consumerResource->_afterDelete($this->consumerMock)
        );
    }

    public function testGetTimeInSecondsSinceCreation(): void
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('reset')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('columns')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('where')->willReturn($selectMock);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne');
        $this->consumerResource->getTimeInSecondsSinceCreation(1);
    }
}
