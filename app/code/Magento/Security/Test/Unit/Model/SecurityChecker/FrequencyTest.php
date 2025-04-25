<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\SecurityChecker;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory;
use Magento\Security\Model\SecurityChecker\Frequency;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\SecurityChecker\Frequency testing
 */
class FrequencyTest extends TestCase
{
    /**
     * @var  Frequency
     */
    protected $model;

    /**
     * @var ConfigInterface
     */
    protected $securityConfigMock;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection
     */
    protected $collectionMock;

    /**
     * @var DateTime
     */
    protected $dateTimeMock;

    /**
     * @var  ObjectManager
     */
    protected $objectManager;

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
        $this->objectManager = new ObjectManager($this);
        $this->securityConfigMock =  $this->getMockBuilder(ConfigInterface::class)
            ->addMethods(['getScopeByEventType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->securityConfigMock->expects($this->any())
            ->method('getScopeByEventType')
            ->willReturnMap(
                [
                    [0, 1],
                    [1, 0]
                ]
            );

        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->collectionMock = $this->createPartialMock(
            Collection::class,
            ['addFieldToFilter', 'filterLastItem', 'getFirstItem']
        );

        $this->dateTimeMock =  $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->remoteAddressMock =  $this->getMockBuilder(RemoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            Frequency::class,
            [
                'securityConfig' => $this->securityConfigMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'dateTime' => $this->dateTimeMock,
                'remoteAddress' => $this->remoteAddressMock
            ]
        );
    }

    /**
     * @param int $securityEventType
     * @param int $requestsMethod
     * @dataProvider dataProviderSecurityEventTypeWithRequestsMethod
     */
    public function testCheck($securityEventType, $requestsMethod)
    {
        $limitTimeBetweenPasswordResetRequests = 600;
        $timestamp = time();

        $this->prepareTestCheck($requestsMethod, $limitTimeBetweenPasswordResetRequests);

        $this->dateTimeMock->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($timestamp);

        /** @var PasswordResetRequestEvent $record */
        $record = $this->objectManager->getObject(PasswordResetRequestEvent::class);
        $record->setCreatedAt(
            date("Y-m-d H:i:s", $timestamp - $limitTimeBetweenPasswordResetRequests)
        );

        $this->collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($record);

        $this->model->check($securityEventType);
    }

    /**
     * @param int $securityEventType
     * @param int $requestsMethod
     * @dataProvider dataProviderSecurityEventTypeWithRequestsMethod
     */
    public function testCheckException($securityEventType, $requestsMethod)
    {
        $this->expectException('Magento\Framework\Exception\SecurityViolationException');
        $limitTimeBetweenPasswordResetRequests = 600;
        $timestamp = time();

        $this->prepareTestCheck($requestsMethod, $limitTimeBetweenPasswordResetRequests);

        $this->dateTimeMock->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($timestamp);

        /** @var PasswordResetRequestEvent $record */
        $record = $this->objectManager->getObject(PasswordResetRequestEvent::class);
        $record->setCreatedAt(
            date("Y-m-d H:i:s", $timestamp - $limitTimeBetweenPasswordResetRequests + 1)
        );

        $this->collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($record);

        $this->model->check($securityEventType);

        $this->expectExceptionMessage(
            'We received too many requests for password resets. '
            . 'Please wait and try again later or contact test@host.com.'
        );
    }

    /**
     * @return array
     */
    public static function dataProviderSecurityEventTypeWithRequestsMethod()
    {
        return [
            [
                PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST,
                ResetMethod::OPTION_BY_IP_AND_EMAIL
            ],
            [
                PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST,
                ResetMethod::OPTION_BY_IP
            ],
            [
                PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST,
                ResetMethod::OPTION_BY_EMAIL
            ],
            [
                PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
                ResetMethod::OPTION_BY_IP_AND_EMAIL
            ],
            [
                PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
                ResetMethod::OPTION_BY_IP
            ],
            [
                PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
                ResetMethod::OPTION_BY_EMAIL
            ],
        ];
    }

    /**
     * @param int $requestsMethod
     * @param int $limitTimeBetweenPasswordResetRequests
     */
    protected function prepareTestCheck($requestsMethod, $limitTimeBetweenPasswordResetRequests)
    {
        $this->remoteAddressMock->expects($this->once())
            ->method('getRemoteAddress')
            ->willReturn(12345);

        $this->securityConfigMock->expects($this->any())
            ->method('getPasswordResetProtectionType')
            ->willReturn($requestsMethod);

        $this->securityConfigMock->expects($this->once())
            ->method('getMinTimeBetweenPasswordResetRequests')
            ->willReturn($limitTimeBetweenPasswordResetRequests);

        $this->securityConfigMock->expects($this->any())
            ->method('getCustomerServiceEmail')
            ->willReturn('test@host.com');

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->collectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $this->collectionMock->expects($this->once())
            ->method('filterLastItem')
            ->willReturnSelf();
    }
}
