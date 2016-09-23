<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    private $model;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * Set up
     *
     * @return void
     */

    /**
     * @var \Magento\Framework\Json\JsonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;
    
    protected function setUp()
    {
        $this->cache = $this->getMockBuilder(\Magento\Framework\Cache\FrontendInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManager->getObject(
            NotificationStorage::class,
            [
                'cache' => $this->cache
            ]
        );

        $this->jsonMock = $this->getMock(\Magento\Framework\Json\JsonInterface::class);
        $objectManager->setBackwardCompatibleProperty($this->model, 'json', $this->jsonMock);
    }

    public function testAdd()
    {
        $customerId = 1;
        $notificationType = 'some_type';
        $this->jsonMock->expects($this->once())
            ->method('encode')
            ->with(
                [
                    'customer_id' => $customerId,
                    'notification_type' => $notificationType
                ]
            )
            ->willReturn(
                '{"customer_id":1,"notification_type":"some_type"}'
            );
        $this->cache->expects($this->once())
            ->method('save')
            ->with(
                json_encode([
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
