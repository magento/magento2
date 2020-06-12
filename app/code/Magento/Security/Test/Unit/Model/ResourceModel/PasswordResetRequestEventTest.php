<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent testing
 */
class PasswordResetRequestEventTest extends TestCase
{
    /** @var PasswordResetRequestEvent */
    protected $model;

    /** @var DateTime */
    protected $dateTimeMock;

    /** @var ResourceConnection */
    protected $resourceMock;

    /** @var AdapterInterface */
    protected $dbAdapterMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->dateTimeMock = $this->createMock(DateTime::class);

        $this->resourceMock = $this->createMock(ResourceConnection::class);

        $this->dbAdapterMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->model = $objectManager->getObject(
            PasswordResetRequestEvent::class,
            [
                'resource' => $this->resourceMock,
                'dateTime' => $this->dateTimeMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testDeleteRecordsOlderThen()
    {
        $timestamp = 12345;

        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->dbAdapterMock);

        $this->dbAdapterMock->expects($this->once())
            ->method('delete')
            ->with($this->model->getMainTable(), ['created_at < ?' => $this->dateTimeMock->formatDate($timestamp)])
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->deleteRecordsOlderThen($timestamp));
    }
}
