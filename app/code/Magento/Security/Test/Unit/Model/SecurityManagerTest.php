<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\PasswordResetRequestEventFactory;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory;
use Magento\Security\Model\SecurityChecker\SecurityCheckerInterface;
use Magento\Security\Model\SecurityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\SecurityManager testing
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SecurityManagerTest extends TestCase
{
    use MockCreationTrait;

    /** @var  SecurityManager */
    protected $model;

    /** @var ConfigInterface */
    protected $securityConfigMock;

    /** @var CollectionFactory */
    protected $passwordResetRequestEventCollectionFactoryMock;

    /** @var Collection */
    protected $passwordResetRequestEventCollectionMock;

    /** @var PasswordResetRequestEventFactory */
    protected $passwordResetRequestEventFactoryMock;

    /** @var PasswordResetRequestEvent */
    protected $passwordResetRequestEventMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var DateTime
     */
    protected $dateTimeMock;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddressMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->securityConfigMock = $this->createMock(ConfigInterface::class);

        $this->passwordResetRequestEventCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->passwordResetRequestEventCollectionMock = $this->createPartialMock(
            Collection::class,
            ['deleteRecordsOlderThen']
        );

        $this->passwordResetRequestEventFactoryMock = $this->createPartialMock(
            PasswordResetRequestEventFactory::class,
            ['create']
        );

        $this->passwordResetRequestEventMock = $this->createPartialMockWithReflection(
            PasswordResetRequestEvent::class,
            ['setRequestType', 'setAccountReference', 'setIp', 'save']
        );

        $securityChecker = $this->createMock(SecurityCheckerInterface::class);

        $this->eventManagerMock = $this->createMock(ManagerInterface::class);

        $this->dateTimeMock = $this->createMock(DateTime::class);

        $this->remoteAddressMock = $this->createMock(RemoteAddress::class);

        $this->model = new SecurityManager(
            $this->securityConfigMock,
            $this->passwordResetRequestEventFactoryMock,
            $this->passwordResetRequestEventCollectionFactoryMock,
            $this->eventManagerMock,
            $this->dateTimeMock,
            $this->remoteAddressMock,
            [$securityChecker]
        );
    }

    /**
     * @return void
     */
    public function testConstructorException()
    {
        $securityChecker = $this->createMock(MessageManagerInterface::class);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            (string)__('Incorrect Security Checker class. It has to implement SecurityCheckerInterface')
        );

        $this->model->__construct(
            $this->securityConfigMock,
            $this->passwordResetRequestEventFactoryMock,
            $this->passwordResetRequestEventCollectionFactoryMock,
            $this->eventManagerMock,
            $this->dateTimeMock,
            $this->remoteAddressMock,
            [$securityChecker]
        );
    }

    /**
     * @return void
     */
    public function testPerformSecurityCheck()
    {
        $requestType = PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST;
        $accountReference = ResetMethod::OPTION_BY_IP_AND_EMAIL;
        $longIp = 12345;

        $this->remoteAddressMock->expects($this->once())
            ->method('getRemoteAddress')
            ->willReturn($longIp);

        $this->passwordResetRequestEventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->passwordResetRequestEventMock);

        $this->passwordResetRequestEventMock->expects($this->once())
            ->method('setRequestType')
            ->with($requestType)
            ->willReturnSelf();

        $this->passwordResetRequestEventMock->expects($this->once())
            ->method('setAccountReference')
            ->with($accountReference)
            ->willReturnSelf();

        $this->passwordResetRequestEventMock->expects($this->once())
            ->method('setIp')
            ->with($longIp)
            ->willReturnSelf();

        $this->passwordResetRequestEventMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->model->performSecurityCheck($requestType, $accountReference);
    }

    /**
     * @return void
     */
    public function testCleanExpiredRecords()
    {
        $timestamp = time();

        $this->passwordResetRequestEventCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->passwordResetRequestEventCollectionMock);

        $this->dateTimeMock->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($timestamp);

        $this->passwordResetRequestEventCollectionMock->expects($this->once())
            ->method('deleteRecordsOlderThen')
            ->with(
                $timestamp - SecurityManager::SECURITY_CONTROL_RECORDS_LIFE_TIME
            )
            ->willReturnSelf();

        $this->model->cleanExpiredRecords();
    }
}
