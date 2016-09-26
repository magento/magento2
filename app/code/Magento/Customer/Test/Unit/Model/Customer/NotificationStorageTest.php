<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Customer;

use Magento\Customer\Model\Customer\NotificationStorage;

class NotificationStorageTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Json\JsonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cacheMock = $this->getMock(\Magento\Framework\Cache\FrontendInterface::class);
        $this->notificationStorage = $objectManager->getObject(
            NotificationStorage::class,
            ['cache' => $this->cacheMock]
        );
        $this->jsonMock = $this->getMock(\Magento\Framework\Json\JsonInterface::class);
        $objectManager->setBackwardCompatibleProperty($this->notificationStorage, 'json', $this->jsonMock);
    }

    public function testAdd()
    {
        $customerId = 1;
        $notificationType = 'some_type';
        $data = [
            'customer_id' => $customerId,
            'notification_type' => $notificationType
        ];
        $jsonString = json_encode($data);
        $this->jsonMock->expects($this->once())
            ->method('encode')
            ->with($data)
            ->willReturn($jsonString);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with(
                $jsonString,
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
