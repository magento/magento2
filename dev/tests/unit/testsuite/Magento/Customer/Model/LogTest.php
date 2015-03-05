<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

/**
 * Customer log test.
 *
 * @package Magento\Customer\Model
 */
class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Customer log model.
     *
     * @var \Magento\Customer\Model\Log
     */
    protected $log;

    /**
     * @var array
     */
    protected $logData = [
        'log_id' => 234,
        'customer_id' => 369,
        'last_login_at' => '2015-03-04 12:00:00',
        'last_visit_at' => '2015-03-04 12:01:00',
        'last_logout_at' => '2015-03-04 12:05:00',
    ];

    /**
     * @var string
     */
    protected $currentDateTime = '2015-03-04 12:10:00';

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    protected function setUp()
    {
        $customer = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterface', [], [], '', false
        );
        $customer->expects($this->any())->method('getId')->willReturn($this->logData['customer_id']);

        $event = $this->getMock(
            'Magento\Framework\Event', ['getCustomer'], [], '', false
        );
        $event->expects($this->any())->method('getCustomer')->willReturn($customer);

        $this->observer = $this->getMock(
            'Magento\Framework\Event\Observer', ['getEvent'], [], '', false
        );
        $this->observer->expects($this->any())->method('getEvent')->willReturn($event);

        $select = $this->getMock(
            'Magento\Framework\DB\Select', [], [], '', false
        );
        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();
        $select->expects($this->any())->method('order')->willReturnSelf();
        $select->expects($this->any())->method('limit')->willReturnSelf();

        $adapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo',
            ['select', 'insertOnDuplicate', 'lastInsertId', 'fetchRow'],
            [],
            '',
            false
        );
        $adapter->expects($this->any())->method('select')->willReturn($select);
        $adapter->expects($this->any())->method('lastInsertId')->willReturn($this->logData['log_id']);
        $adapter->expects($this->any())->method('fetchRow')->willReturn($this->logData);

        $dateTime = $this->getMock(
            'Magento\Framework\Stdlib\DateTime', ['now'], [], '', false
        );
        $dateTime->expects($this->any())->method('now')->willReturn($this->currentDateTime);

        $resource = $this->getMock(
            'Magento\Framework\App\Resource', ['getConnection'], [], '', false
        );
        $resource->expects($this->any())->method('getConnection')->willReturn($adapter);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->log = $objectManagerHelper->getObject(
            'Magento\Customer\Model\Log',
            [
                'resource' => $resource,
                'dateTime' => $dateTime
            ]
        );
    }

    public function testSaveLastLoginAt()
    {
        $log = clone $this->log;

        $log->setId($this->logData['log_id']);
        $log->setCustomerId($this->logData['customer_id']);
        $log->setLastLoginAt($this->currentDateTime);

        $this->assertEquals(
            $log, $this->log->saveLastLoginAt($this->observer)
        );
    }

    public function testSaveLastLogoutAt()
    {
        $log = clone $this->log;

        $log->setId($this->logData['log_id']);
        $log->setCustomerId($this->logData['customer_id']);
        $log->setLastLogoutAt($this->currentDateTime);

        $this->assertEquals(
            $log, $this->log->saveLastLogoutAt($this->observer)
        );
    }

    public function testSave()
    {
        $log = clone $this->log;

        $log->setId($this->logData['log_id']);

        $this->assertEquals($log, $this->log->save());
    }

    public function testLoadByCustomer()
    {
        $log = clone $this->log;

        $log->setData($this->logData);

        $this->assertEquals(
            $log, $this->log->loadByCustomer($this->logData['customer_id'])
        );
    }
}
