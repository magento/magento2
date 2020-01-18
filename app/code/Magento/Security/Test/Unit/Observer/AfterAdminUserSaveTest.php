<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Observer;

/**
 * Test class for \Magento\Security\Observer\AfterAdminUserSave
 */
class AfterAdminUserSaveTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Security\Model\UserExpirationFactory
     */
    private $userExpirationFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Security\Model\ResourceModel\UserExpiration
     */
    private $userExpirationResourceMock;

    /**
     * @var \Magento\Security\Observer\AfterAdminUserSave
     */
    private $observer;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event\Observer
     */
    private $eventObserverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event
     */
    private $eventMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\User\Model\User
     */
    private $userMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Security\Model\UserExpiration
     */
    private $userExpirationMock;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->userExpirationFactoryMock = $this->createMock(\Magento\Security\Model\UserExpirationFactory::class);
        $this->userExpirationResourceMock = $this->createPartialMock(
            \Magento\Security\Model\ResourceModel\UserExpiration::class,
            ['load', 'save', 'delete']
        );
        $this->observer = $this->objectManager->getObject(
            \Magento\Security\Observer\AfterAdminUserSave::class,
            [
                'userExpirationFactory' => $this->userExpirationFactoryMock,
                'userExpirationResource' => $this->userExpirationResourceMock,
            ]
        );
        $this->eventObserverMock = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $this->eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getObject']);
        $this->userMock = $this->createPartialMock(\Magento\User\Model\User::class, ['getId', 'getExpiresAt']);
        $this->userExpirationMock = $this->createPartialMock(
            \Magento\Security\Model\UserExpiration::class,
            ['getId', 'getExpiresAt', 'setId', 'setExpiresAt']
        );
    }

    public function testSaveNewUserExpiration()
    {
        $userId = '123';
        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getObject')->willReturn($this->userMock);
        $this->userMock->expects(static::exactly(3))->method('getId')->willReturn($userId);
        $this->userMock->expects(static::once())->method('getExpiresAt')->willReturn($this->getExpiresDateTime());
        $this->userExpirationFactoryMock->expects(static::once())->method('create')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationResourceMock->expects(static::once())->method('load')
            ->willReturn($this->userExpirationMock);

        $this->userExpirationMock->expects(static::once())->method('getId')->willReturn(null);
        $this->userExpirationMock->expects(static::once())->method('setId')->willReturn($this->userExpirationMock);
        $this->userExpirationMock->expects(static::once())->method('setExpiresAt')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationResourceMock->expects(static::once())->method('save')
            ->willReturn($this->userExpirationResourceMock);
        $this->observer->execute($this->eventObserverMock);
    }

    /**
     * @throws \Exception
     */
    public function testClearUserExpiration()
    {
        $userId = '123';
        $this->userExpirationMock->setId($userId);

        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getObject')->willReturn($this->userMock);
        $this->userMock->expects(static::exactly(2))->method('getId')->willReturn($userId);
        $this->userMock->expects(static::once())->method('getExpiresAt')->willReturn(null);
        $this->userExpirationFactoryMock->expects(static::once())->method('create')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationResourceMock->expects(static::once())->method('load')
            ->willReturn($this->userExpirationMock);

        $this->userExpirationMock->expects(static::once())->method('getId')->willReturn($userId);
        $this->userExpirationResourceMock->expects(static::once())->method('delete')
            ->willReturn($this->userExpirationResourceMock);
        $this->observer->execute($this->eventObserverMock);
    }

    public function testChangeUserExpiration()
    {
        $userId = '123';
        $this->userExpirationMock->setId($userId);

        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getObject')->willReturn($this->userMock);
        $this->userMock->expects(static::exactly(2))->method('getId')->willReturn($userId);
        $this->userMock->expects(static::once())->method('getExpiresAt')->willReturn($this->getExpiresDateTime());
        $this->userExpirationFactoryMock->expects(static::once())->method('create')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationResourceMock->expects(static::once())->method('load')
            ->willReturn($this->userExpirationMock);

        $this->userExpirationMock->expects(static::once())->method('getId')->willReturn($userId);
        $this->userExpirationMock->expects(static::once())->method('setExpiresAt')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationResourceMock->expects(static::once())->method('save')
            ->willReturn($this->userExpirationResourceMock);
        $this->observer->execute($this->eventObserverMock);
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getExpiresDateTime()
    {
        $testDate = new \DateTime();
        $testDate->modify('+10 days');
        return $testDate->format('Y-m-d H:i:s');
    }
}
