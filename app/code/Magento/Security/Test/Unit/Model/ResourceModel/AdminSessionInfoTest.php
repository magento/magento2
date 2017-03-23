<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\ResourceModel\AdminSessionInfo testing
 */
class AdminSessionInfoTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Security\Model\ResourceModel\AdminSessionInfo */
    protected $model;

    /** @var \Magento\Framework\Stdlib\DateTime */
    protected $dateTimeMock;

    /** @var \Magento\Framework\App\ResourceConnection */
    protected $resourceMock;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    protected $dbAdapterMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->dateTimeMock = $this->getMock(
            \Magento\Framework\Stdlib\DateTime::class,
            [],
            [],
            '',
            false
        );

        $this->resourceMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );

        $this->dbAdapterMock = $this->getMock(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            [],
            '',
            false
        );

        $this->model = $objectManager->getObject(
            \Magento\Security\Model\ResourceModel\AdminSessionInfo::class,
            [
                'resource' => $this->resourceMock,
                'dateTime' => $this->dateTimeMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testDeleteSessionsOlderThen()
    {
        $timestamp = 12345;

        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->dbAdapterMock);

        $this->dbAdapterMock->expects($this->once())
            ->method('delete')
            ->with($this->model->getMainTable(), ['updated_at < ?' => $this->dateTimeMock->formatDate($timestamp)])
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->deleteSessionsOlderThen($timestamp));
    }

    /**
     * @return void
     */
    public function testUpdateStatusByUserId()
    {
        $status = 2;
        $userId = 10;
        $withStatuses = [1, 5];
        $excludedSessionIds = [20, 21, 22];
        $updateOlderThen = '2015-12-31 23:59:59';

        $whereStatement = [
            'updated_at > ?' => $this->dateTimeMock->formatDate($updateOlderThen),
            'user_id = ?' => (int) $userId,
        ];
        if (!empty($excludedSessionIds)) {
            $whereStatement['session_id NOT IN (?)'] = $excludedSessionIds;
        }
        if (!empty($withStatuses)) {
            $whereStatement['status IN (?)'] = $withStatuses;
        }

        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->dbAdapterMock);

        $this->dbAdapterMock->expects($this->once())
            ->method('update')
            ->with($this->model->getMainTable(), ['status' => $status], $whereStatement)
            ->willReturnSelf();

        $this->model->updateStatusByUserId($status, $userId, $withStatuses, $excludedSessionIds, $updateOlderThen);
    }
}
