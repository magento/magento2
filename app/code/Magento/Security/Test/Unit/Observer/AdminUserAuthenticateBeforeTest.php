<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Api\Data\UserExpirationInterface;
use Magento\Security\Model\UserExpirationManager;
use Magento\Security\Observer\AdminUserAuthenticateBefore;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Security\Observer\AdminUserAuthenticateBefore
 */
class AdminUserAuthenticateBeforeTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject|UserExpirationManager
     */
    private $userExpirationManagerMock;

    /**
     * @var MockObject|User
     */
    private $userMock;

    /**
     * @var MockObject|UserFactory
     */
    private $userFactoryMock;

    /**
     * @var AdminUserAuthenticateBefore
     */
    private $observer;

    /**
     * @var MockObject|Observer ::class
     */
    private $eventObserverMock;

    /**
     * @var MockObject|Event
     */
    private $eventMock;

    /**
     * @var MockObject|UserExpirationInterface
     */
    private $userExpirationMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->userExpirationManagerMock = $this->createPartialMock(
            UserExpirationManager::class,
            ['isUserExpired', 'deactivateExpiredUsersById']
        );
        $this->userFactoryMock = $this->createPartialMock(UserFactory::class, ['create']);
        $this->userMock = $this->createPartialMock(User::class, ['loadByUsername', 'getId']);
        $this->observer = $this->objectManager->getObject(
            AdminUserAuthenticateBefore::class,
            [
                'userExpirationManager' => $this->userExpirationManagerMock,
                'userFactory' => $this->userFactoryMock,
            ]
        );
        $this->eventObserverMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getUsername'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->userExpirationMock = $this->createPartialMock(
            UserExpirationInterface::class,
            [
                'getUserId',
                'getExpiresAt',
                'setUserId',
                'setExpiresAt',
                'getExtensionAttributes',
                'setExtensionAttributes'
            ]
        );
    }

    public function testWithExpiredUser()
    {
        $this->expectException('Magento\Framework\Exception\Plugin\AuthenticationException');
        $this->expectExceptionMessage(
            'The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later'
        );
        $adminUserId = '123';
        $username = 'testuser';
        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getUsername')->willReturn($username);
        $this->userFactoryMock->expects(static::once())->method('create')->willReturn($this->userMock);
        $this->userMock->expects(static::once())->method('loadByUsername')->willReturnSelf();

        $this->userExpirationManagerMock->expects(static::once())
            ->method('isUserExpired')
            ->with($adminUserId)
            ->willReturn(true);
        $this->userMock->expects(static::exactly(3))->method('getId')->willReturn($adminUserId);
        $this->userExpirationManagerMock->expects(static::once())
            ->method('deactivateExpiredUsersById')
            ->with([$adminUserId]);
        $this->observer->execute($this->eventObserverMock);
    }

    public function testWithNonExpiredUser()
    {
        $adminUserId = '123';
        $username = 'testuser';
        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getUsername')->willReturn($username);
        $this->userFactoryMock->expects(static::once())->method('create')->willReturn($this->userMock);
        $this->userMock->expects(static::once())->method('loadByUsername')->willReturnSelf();
        $this->userMock->expects(static::exactly(2))->method('getId')->willReturn($adminUserId);
        $this->userExpirationManagerMock->expects(static::once())
            ->method('isUserExpired')
            ->with($adminUserId)
            ->willReturn(false);
        $this->observer->execute($this->eventObserverMock);
    }
}
