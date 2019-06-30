<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Observer;

/**
 * Test class for \Magento\Security\Observer\AfterAdminUserLoad
 */
class AfterAdminUserLoadTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Security\Observer\AfterAdminUserLoad
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

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->userExpirationFactoryMock = $this->createMock(\Magento\Security\Model\UserExpirationFactory::class);
        $this->userExpirationResourceMock = $this->createPartialMock(
            \Magento\Security\Model\ResourceModel\UserExpiration::class,
            ['load']
        );
        $this->observer = $this->objectManager->getObject(
            \Magento\Security\Observer\AfterAdminUserLoad::class,
            [
                'userExpirationFactory' => $this->userExpirationFactoryMock,
                'userExpirationResource' => $this->userExpirationResourceMock,
            ]
        );

        $this->eventObserverMock = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $this->eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getObject']);
        $this->userMock = $this->createPartialMock(\Magento\User\Model\User::class, ['getId', 'setExpiresAt']);
        $this->userExpirationMock = $this->createPartialMock(
            \Magento\Security\Model\UserExpiration::class,
            ['getExpiresAt']
        );
    }

    public function testWithExpiredUser()
    {
        $userId = '123';
        $testDate = new \DateTime();
        $testDate->modify('+10 days');
        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getObject')->willReturn($this->userMock);
        $this->userMock->expects(static::exactly(2))->method('getId')->willReturn($userId);
        $this->userExpirationFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationResourceMock->expects(static::once())
            ->method('load')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationMock->expects(static::exactly(2))
            ->method('getExpiresAt')
            ->willReturn($testDate->format('Y-m-d H:i:s'));
        $this->userMock->expects(static::once())
            ->method('setExpiresAt')
            ->willReturn($this->userMock);
        $this->observer->execute($this->eventObserverMock);
    }

    public function testWithNonExpiredUser()
    {
        $userId = '123';
        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getObject')->willReturn($this->userMock);
        $this->userMock->expects(static::exactly(2))->method('getId')->willReturn($userId);
        $this->userExpirationFactoryMock->expects(static::once())->method('create')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationResourceMock->expects(static::once())->method('load')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationMock->expects(static::once())
            ->method('getExpiresAt')
            ->willReturn(null);
        $this->observer->execute($this->eventObserverMock);
    }
}
