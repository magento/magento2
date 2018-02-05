<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent testing
 */
class PasswordResetRequestEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent */
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
            '\Magento\Framework\Stdlib\DateTime',
            [],
            [],
            '',
            false
        );

        $this->resourceMock = $this->getMock(
            '\Magento\Framework\App\ResourceConnection',
            [],
            [],
            '',
            false
        );

        $this->dbAdapterMock = $this->getMock(
            '\Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            [],
            '',
            false
        );

        $this->model = $objectManager->getObject(
            '\Magento\Security\Model\ResourceModel\PasswordResetRequestEvent',
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
