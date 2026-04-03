<?php

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Observer\CustomerEmailChangedObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\CustomerTokenService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CustomerEmailChangedObserver
 */
class CustomerEmailChangedObserverTest extends TestCase
{
    /**
     * @var CustomerEmailChangedObserver
     */
    private $observer;

    /**
     * @var SessionCleanerInterface|MockObject
     */
    private $sessionCleanerMock;

    /**
     * @var CustomerTokenService|MockObject
     */
    private $customerTokenServiceMock;

    /**
     * @var EmailNotificationInterface|MockObject
     */
    private $emailNotificationMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->sessionCleanerMock = $this->createMock(SessionCleanerInterface::class);
        $this->customerTokenServiceMock = $this->createMock(CustomerTokenService::class);
        $this->emailNotificationMock = $this->createMock(EmailNotificationInterface::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->createMock(Event::class);
        $this->customerMock = $this->createMock(CustomerInterface::class);

        $this->observer = new CustomerEmailChangedObserver(
            $this->sessionCleanerMock,
            $this->customerTokenServiceMock,
            $this->emailNotificationMock
        );
    }

    /**
     * Test successful execution with valid customer ID
     */
    public function testExecuteWithValidCustomerId(): void
    {
        $customerId = 123;
        $originalEmail = 'old@example.com';

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(function ($key) use ($originalEmail) {
                if ($key === 'customer') {
                    return $this->customerMock;
                }
                if ($key === 'original_customer_email') {
                    return $originalEmail;
                }
                return null;
            });

        $this->sessionCleanerMock->expects($this->once())
            ->method('clearFor')
            ->with($customerId);

        $this->customerTokenServiceMock->expects($this->once())
            ->method('revokeCustomerAccessToken')
            ->with($customerId);

        $this->emailNotificationMock->expects($this->once())
            ->method('credentialsChanged')
            ->with($this->customerMock, $originalEmail, false);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test execution with null customer ID (early return)
     */
    public function testExecuteWithNullCustomerId(): void
    {
        $originalEmail = 'old@example.com';

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(function ($key) use ($originalEmail) {
                if ($key === 'customer') {
                    return $this->customerMock;
                }
                if ($key === 'original_customer_email') {
                    return $originalEmail;
                }
                return null;
            });

        $this->sessionCleanerMock->expects($this->never())
            ->method('clearFor');

        $this->customerTokenServiceMock->expects($this->never())
            ->method('revokeCustomerAccessToken');

        $this->emailNotificationMock->expects($this->never())
            ->method('credentialsChanged');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test execution with zero customer ID (early return)
     */
    public function testExecuteWithZeroCustomerId(): void
    {
        $originalEmail = 'old@example.com';

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn(0);

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(function ($key) use ($originalEmail) {
                if ($key === 'customer') {
                    return $this->customerMock;
                }
                if ($key === 'original_customer_email') {
                    return $originalEmail;
                }
                return null;
            });

        $this->sessionCleanerMock->expects($this->never())
            ->method('clearFor');

        $this->customerTokenServiceMock->expects($this->never())
            ->method('revokeCustomerAccessToken');

        $this->emailNotificationMock->expects($this->never())
            ->method('credentialsChanged');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test execution with empty string customer ID (early return)
     */
    public function testExecuteWithEmptyStringCustomerId(): void
    {
        $originalEmail = 'old@example.com';

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn('');

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(function ($key) use ($originalEmail) {
                if ($key === 'customer') {
                    return $this->customerMock;
                }
                if ($key === 'original_customer_email') {
                    return $originalEmail;
                }
                return null;
            });

        $this->sessionCleanerMock->expects($this->never())
            ->method('clearFor');

        $this->customerTokenServiceMock->expects($this->never())
            ->method('revokeCustomerAccessToken');

        $this->emailNotificationMock->expects($this->never())
            ->method('credentialsChanged');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test execution when SessionCleaner throws LocalizedException
     */
    public function testExecuteWhenSessionCleanerThrowsLocalizedException(): void
    {
        $customerId = 123;
        $originalEmail = 'old@example.com';
        $exception = new LocalizedException(__('Session cleaning failed'));

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(function ($key) use ($originalEmail) {
                if ($key === 'customer') {
                    return $this->customerMock;
                }
                if ($key === 'original_customer_email') {
                    return $originalEmail;
                }
                return null;
            });

        $this->sessionCleanerMock->expects($this->once())
            ->method('clearFor')
            ->with($customerId)
            ->willThrowException($exception);

        $this->customerTokenServiceMock->expects($this->never())
            ->method('revokeCustomerAccessToken');

        $this->emailNotificationMock->expects($this->never())
            ->method('credentialsChanged');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while logging out customer.');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test execution when CustomerTokenService throws LocalizedException
     */
    public function testExecuteWhenTokenServiceThrowsLocalizedException(): void
    {
        $customerId = 123;
        $originalEmail = 'old@example.com';
        $exception = new LocalizedException(__('Token revocation failed'));

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(function ($key) use ($originalEmail) {
                if ($key === 'customer') {
                    return $this->customerMock;
                }
                if ($key === 'original_customer_email') {
                    return $originalEmail;
                }
                return null;
            });

        $this->sessionCleanerMock->expects($this->once())
            ->method('clearFor')
            ->with($customerId);

        $this->customerTokenServiceMock->expects($this->once())
            ->method('revokeCustomerAccessToken')
            ->with($customerId)
            ->willThrowException($exception);

        $this->emailNotificationMock->expects($this->never())
            ->method('credentialsChanged');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Something went wrong while logging out customer.');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test execution with string customer ID (should be cast to int)
     */
    public function testExecuteWithStringCustomerId(): void
    {
        $customerId = '456';
        $expectedCustomerId = 456;
        $originalEmail = 'old@example.com';

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(function ($key) use ($originalEmail) {
                if ($key === 'customer') {
                    return $this->customerMock;
                }
                if ($key === 'original_customer_email') {
                    return $originalEmail;
                }
                return null;
            });

        $this->sessionCleanerMock->expects($this->once())
            ->method('clearFor')
            ->with($expectedCustomerId);

        $this->customerTokenServiceMock->expects($this->once())
            ->method('revokeCustomerAccessToken')
            ->with($expectedCustomerId);

        $this->emailNotificationMock->expects($this->once())
            ->method('credentialsChanged')
            ->with($this->customerMock, $originalEmail, false);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test that all services are called in correct order
     */
    public function testExecuteCallsServicesInCorrectOrder(): void
    {
        $customerId = 789;
        $originalEmail = 'old@example.com';

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->observerMock->expects($this->exactly(2))
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(function ($key) use ($originalEmail) {
                if ($key === 'customer') {
                    return $this->customerMock;
                }
                if ($key === 'original_customer_email') {
                    return $originalEmail;
                }
                return null;
            });

        // Use a counter to verify call order
        $callOrder = [];

        $this->sessionCleanerMock->expects($this->once())
            ->method('clearFor')
            ->with($customerId)
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'sessionCleaner';
            });

        $this->customerTokenServiceMock->expects($this->once())
            ->method('revokeCustomerAccessToken')
            ->with($customerId)
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'tokenService';
            });

        $this->emailNotificationMock->expects($this->once())
            ->method('credentialsChanged')
            ->with($this->customerMock, $originalEmail, false)
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'emailNotification';
            });

        $this->observer->execute($this->observerMock);

        // Verify services are called in correct order
        $this->assertEquals(['sessionCleaner', 'tokenService', 'emailNotification'], $callOrder);
    }
}
