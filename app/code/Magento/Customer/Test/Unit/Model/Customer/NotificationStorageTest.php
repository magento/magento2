<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Customer;

use Magento\Customer\Model\Customer\NotificationStorage;

/**
 * Class NotificationStorageTest
 *
 * Test for class \Magento\Customer\Model\Customer\NotificationStorage
 */
class NotificationStorageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var NotificationStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->cache = $this->getMockBuilder('Magento\Framework\Cache\FrontendInterface')->getMockForAbstractClass();
        $this->model = new NotificationStorage($this->cache);
    }

    public function testAdd()
    {
        $customerId = 1;
        $notificationType = 'some_type';
        $this->cache->expects($this->once())
            ->method('save')
            ->with(
                serialize([
                    'customer_id' => $customerId,
                    'notification_type' => $notificationType
                ]),
                $this->getCacheKey($notificationType, $customerId)
            );
        $this->model->add($notificationType, $customerId);
    }

    public function testIsExists()
    {
        $customerId = 1;
        $notificationType = 'some_type';
        $this->cache->expects($this->once())
            ->method('test')
            ->with($this->getCacheKey($notificationType, $customerId))
            ->willReturn(true);
        $this->assertTrue($this->model->isExists($notificationType, $customerId));
    }

    public function testRemove()
    {
        $customerId = 1;
        $notificationType = 'some_type';
        $this->cache->expects($this->once())
            ->method('remove')
            ->with($this->getCacheKey($notificationType, $customerId));
        $this->model->remove($notificationType, $customerId);
    }

    /**
     * Retrieve cache key
     *
     * @param string $notificationType
     * @param string $customerId
     * @return string
     */
    protected function getCacheKey($notificationType, $customerId)
    {
        return 'notification_' . $notificationType . '_' . $customerId;
    }
}
