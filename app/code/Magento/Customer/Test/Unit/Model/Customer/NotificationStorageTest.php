<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Customer;

use Magento\Customer\Model\Customer\NotificationStorage;

class NotificationStorageTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cacheMock = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);
        $this->notificationStorage = $objectManager->getObject(
            NotificationStorage::class,
            ['cache' => $this->cacheMock]
        );
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $objectManager->setBackwardCompatibleProperty($this->notificationStorage, 'serializer', $this->serializerMock);
    }

    public function testAdd()
    {
        $customerId = 1;
        $notificationType = 'some_type';
        $data = [
            'customer_id' => $customerId,
            'notification_type' => $notificationType
        ];
        $serializedData = 'serialized data';
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with(
                $serializedData,
                $this->getCacheKey($notificationType, $customerId)
            );
        $this->notificationStorage->add($notificationType, $customerId);
    }

    public function testIsExists()
    {
        $customerId = 1;
        $notificationType = 'some_type';
        $this->cacheMock->expects($this->once())
            ->method('test')
            ->with($this->getCacheKey($notificationType, $customerId))
            ->willReturn(true);
        $this->assertTrue($this->notificationStorage->isExists($notificationType, $customerId));
    }

    public function testRemove()
    {
        $customerId = 1;
        $notificationType = 'some_type';
        $this->cacheMock->expects($this->once())
            ->method('remove')
            ->with($this->getCacheKey($notificationType, $customerId));
        $this->notificationStorage->remove($notificationType, $customerId);
    }

    /**
     * Get cache key
     *
     * @param string $notificationType
     * @param string $customerId
     * @return string
     */
    private function getCacheKey($notificationType, $customerId)
    {
        return 'notification_' . $notificationType . '_' . $customerId;
    }
}
