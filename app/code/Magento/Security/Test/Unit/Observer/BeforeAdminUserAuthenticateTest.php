<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Observer;

/**
 * Test for \Magento\Security\Observer\AdminUserAuthenticateBefore
 *
 * @package Magento\Security\Test\Unit\Observer
 */
class BeforeAdminUserAuthenticateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Security\Model\UserExpirationManager
     */
    private $userExpirationManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\User\Model\User
     */
    private $userMock;

    /**
     * @var \Magento\Security\Observer\AdminUserAuthenticateBefore
     */
    private $observer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event\Observer::class
     */
    private $eventObserverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event
     */
    private $eventMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Security\Model\UserExpiration
     */
    private $userExpirationMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->userExpirationManagerMock = $this->createPartialMock(
            \Magento\Security\Model\UserExpirationManager::class,
            ['userIsExpired', 'deactivateExpiredUsers']
        );
        $this->userMock = $this->createPartialMock(\Magento\User\Model\User::class, ['loadByUsername', 'getId']);
        $this->observer = $this->objectManager->getObject(
            \Magento\Security\Observer\AdminUserAuthenticateBefore::class,
            [
                'userExpirationManager' => $this->userExpirationManagerMock,
                'user' => $this->userMock,
            ]
        );
        $this->eventObserverMock = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $this->eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getUsername']);
        $this->userExpirationMock = $this->createPartialMock(
            \Magento\Security\Model\UserExpiration::class,
            ['getId', 'getExpiresAt', 'setId', 'setExpiresAt']
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\Plugin\AuthenticationException
     * @expectedExceptionMessage The account sign-in was incorrect or your account is disabled temporarily.
     *  Please wait and try again later
     */
    public function testWithExpiredUser()
    {
        $adminUserId = 123;
        $username = 'testuser';
        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getUsername')->willReturn($username);
        $this->userMock->expects(static::once())->method('loadByUsername')->willReturn($this->userMock);

        $this->userExpirationManagerMock->expects(static::once())
            ->method('userIsExpired')
            ->with($this->userMock)
            ->willReturn(true);
        $this->userMock->expects(static::exactly(2))->method('getId')->willReturn($adminUserId);
        $this->userExpirationManagerMock->expects(static::once())
            ->method('deactivateExpiredUsers')
            ->with([$adminUserId])
            ->willReturn(null);
        $this->observer->execute($this->eventObserverMock);
    }

    public function testWithNonExpiredUser()
    {
        $adminUserId = 123;
        $username = 'testuser';
        $this->eventObserverMock->expects(static::once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects(static::once())->method('getUsername')->willReturn($username);
        $this->userMock->expects(static::once())->method('loadByUsername')->willReturn($this->userMock);
        $this->userMock->expects(static::once())->method('getId')->willReturn($adminUserId);
        $this->userExpirationManagerMock->expects(static::once())
            ->method('userIsExpired')
            ->with($this->userMock)
            ->willReturn(false);
        $this->observer->execute($this->eventObserverMock);
    }
}
