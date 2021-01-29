<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Test\Unit\Model;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\SecurityManager;

/**
 * Test class for \Magento\Security\Model\SecurityManager testing
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SecurityManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Security\Model\SecurityManager */
    protected $model;

    /** @var ConfigInterface */
    protected $securityConfigMock;

    /** @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory */
    protected $passwordResetRequestEventCollectionFactoryMock;

    /** @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection */
    protected $passwordResetRequestEventCollectionMock;

    /** @var \Magento\Security\Model\PasswordResetRequestEventFactory */
    protected $passwordResetRequestEventFactoryMock;

    /** @var \Magento\Security\Model\PasswordResetRequestEvent */
    protected $passwordResetRequestEventMock;

    /** @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var DateTime
     */
    protected $dateTimeMock;

    /*
     * @var RemoteAddress
     */
    protected $remoteAddressMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->securityConfigMock =  $this->getMockBuilder(\Magento\Security\Model\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->passwordResetRequestEventCollectionFactoryMock = $this->createPartialMock(
            \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory::class,
            ['create']
        );

        $this->passwordResetRequestEventCollectionMock = $this->createPartialMock(
            \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection::class,
            ['deleteRecordsOlderThen']
        );

        $this->passwordResetRequestEventFactoryMock = $this->createPartialMock(
            \Magento\Security\Model\PasswordResetRequestEventFactory::class,
            ['create']
        );

        $this->passwordResetRequestEventMock = $this->createPartialMock(
            \Magento\Security\Model\PasswordResetRequestEvent::class,
            ['setRequestType', 'setAccountReference', 'setIp', 'save']
        );

        $securityChecker = $this->createMock(\Magento\Security\Model\SecurityChecker\SecurityCheckerInterface::class);

        $this->eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );

        $this->dateTimeMock =  $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->remoteAddressMock =  $this->getMockBuilder(RemoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            SecurityManager::class,
            [
                'securityConfig' => $this->securityConfigMock,
                'passwordResetRequestEventFactory' => $this->passwordResetRequestEventFactoryMock,
                'passwordResetRequestEventCollectionFactory' => $this->passwordResetRequestEventCollectionFactoryMock,
                'eventManager' => $this->eventManagerMock,
                'securityCheckers' => [$securityChecker],
                'dateTime' => $this->dateTimeMock,
                'remoteAddress' => $this->remoteAddressMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testConstructorException()
    {
        $securityChecker = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
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
        $requestType = \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST;
        $accountReference = \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP_AND_EMAIL;
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
                $timestamp - \Magento\Security\Model\SecurityManager::SECURITY_CONTROL_RECORDS_LIFE_TIME
            )
            ->willReturnSelf();

        $this->model->cleanExpiredRecords();
    }
}
