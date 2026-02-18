<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Security\Model\ResourceModel\UserExpiration;
use Magento\Security\Model\UserExpirationFactory;
use Magento\Security\Observer\AfterAdminUserSave;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Observer\AfterAdminUserSave
 */
class AfterAdminUserSaveTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var MockObject|UserExpirationFactory
     */
    private $userExpirationFactoryMock;

    /**
     * @var MockObject|UserExpiration
     */
    private $userExpirationResourceMock;

    /**
     * @var AfterAdminUserSave
     */
    private $observer;

    /**
     * @var MockObject|Observer
     */
    private $eventObserverMock;

    /**
     * @var MockObject|Event
     */
    private $eventMock;

    /**
     * @var MockObject|User
     */
    private $userMock;

    /**
     * @var MockObject|\Magento\Security\Model\UserExpiration
     */
    private $userExpirationMock;

    protected function setUp(): void
    {
        $this->userExpirationFactoryMock = $this->createMock(UserExpirationFactory::class);
        $this->userExpirationResourceMock = $this->createPartialMock(
            UserExpiration::class,
            ['load', 'save', 'delete']
        );
        $this->observer = new AfterAdminUserSave(
            $this->userExpirationFactoryMock,
            $this->userExpirationResourceMock
        );
        $this->eventObserverMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getObject']
        );
        $this->userMock = $this->createPartialMockWithReflection(
            User::class,
            ['getExpiresAt', 'getId', 'hasData']
        );
        $this->userExpirationMock = $this->createPartialMock(
            \Magento\Security\Model\UserExpiration::class,
            ['getId', 'getExpiresAt', 'setId', 'setExpiresAt']
        );
    }

    /**
     * @return void
     */
    public function testSaveNewUserExpiration(): void
    {
        $userId = '123';
        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getObject')->willReturn($this->userMock);
        $this->userMock->expects(static::exactly(3))->method('getId')->willReturn($userId);
        $this->userMock->expects(static::once())->method('getExpiresAt')->willReturn($this->getExpiresDateTime());
        $this->userMock->expects(static::once())
            ->method('hasData')
            ->with('expires_at')
            ->willReturn(true);
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
    public function testClearUserExpiration(): void
    {
        $userId = '123';
        $this->userExpirationMock->setId($userId);

        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getObject')->willReturn($this->userMock);
        $this->userMock->expects(static::exactly(2))->method('getId')->willReturn($userId);
        $this->userMock->expects(static::once())->method('getExpiresAt')->willReturn(null);
        $this->userMock->expects(static::once())
            ->method('hasData')
            ->with('expires_at')
            ->willReturn(true);
        $this->userExpirationFactoryMock->expects(static::once())->method('create')
            ->willReturn($this->userExpirationMock);
        $this->userExpirationResourceMock->expects(static::once())->method('load')
            ->willReturn($this->userExpirationMock);

        $this->userExpirationMock->expects(static::once())->method('getId')->willReturn($userId);
        $this->userExpirationResourceMock->expects(static::once())->method('delete')
            ->willReturn($this->userExpirationResourceMock);
        $this->observer->execute($this->eventObserverMock);
    }

    /**
     * @return void
     */
    public function testChangeUserExpiration(): void
    {
        $userId = '123';
        $this->userExpirationMock->setId($userId);

        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getObject')->willReturn($this->userMock);
        $this->userMock->expects(static::exactly(2))->method('getId')->willReturn($userId);
        $this->userMock->expects(static::once())->method('getExpiresAt')->willReturn($this->getExpiresDateTime());
        $this->userMock->expects(static::once())
            ->method('hasData')
            ->with('expires_at')
            ->willReturn(true);
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
     * @return void
     */
    public function testExecuteWithoutUserExpiration(): void
    {
        $userId = '123';
        $this->userExpirationMock->setId($userId);

        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getObject')->willReturn($this->userMock);
        $this->userMock->expects(static::once())->method('getId')->willReturn($userId);
        $this->userMock->expects(static::once())
            ->method('hasData')
            ->with('expires_at')
            ->willReturn(false);
        $this->userExpirationFactoryMock->expects(static::never())->method('create');
        $this->userExpirationResourceMock->expects(static::never())->method('load');

        $this->userExpirationMock->expects(static::never())->method('getId');
        $this->userExpirationMock->expects(static::never())->method('setExpiresAt');
        $this->userExpirationResourceMock->expects(static::never())->method('save');
        $this->observer->execute($this->eventObserverMock);
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getExpiresDateTime(): string
    {
        $testDate = new \DateTime();
        $testDate->modify('+10 days');
        return $testDate->format('Y-m-d H:i:s');
    }
}
