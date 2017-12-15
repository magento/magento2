<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\SecurityChecker;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\ConfigInterface;

/**
 * Test class for \Magento\Security\Model\SecurityChecker\Frequency testing
 */
class FrequencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\Security\Model\SecurityChecker\Frequency
     */
    protected $model;

    /**
     * @var ConfigInterface
     */
    protected $securityConfigMock;

    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection
     */
    protected $collectionMock;

    /**
     * @var DateTime
     */
    protected $dateTimeMock;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /*
     * @var RemoteAddress
     */
    protected $remoteAddressMock;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->securityConfigMock =  $this->getMockBuilder(\Magento\Security\Model\ConfigInterface::class)
            ->setMethods(['getScopeByEventType'])
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
            \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory::class,
            ['create']
        );

        $this->collectionMock = $this->createPartialMock(
            \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection::class,
            ['addFieldToFilter', 'filterLastItem', 'getFirstItem']
        );

        $this->dateTimeMock =  $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->remoteAddressMock =  $this->getMockBuilder(RemoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\Security\Model\SecurityChecker\Frequency::class,
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

        /** @var \Magento\Security\Model\PasswordResetRequestEvent $record */
        $record = $this->objectManager->getObject(\Magento\Security\Model\PasswordResetRequestEvent::class);
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
     * @expectedException \Magento\Framework\Exception\SecurityViolationException
     * @expectedExceptionMessage Too many password reset requests. Please wait and try again or contact test@host.com.
     */
    public function testCheckException($securityEventType, $requestsMethod)
    {
        $limitTimeBetweenPasswordResetRequests = 600;
        $timestamp = time();

        $this->prepareTestCheck($requestsMethod, $limitTimeBetweenPasswordResetRequests);

        $this->dateTimeMock->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($timestamp);

        /** @var \Magento\Security\Model\PasswordResetRequestEvent $record */
        $record = $this->objectManager->getObject(\Magento\Security\Model\PasswordResetRequestEvent::class);
        $record->setCreatedAt(
            date("Y-m-d H:i:s", $timestamp - $limitTimeBetweenPasswordResetRequests + 1)
        );

        $this->collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($record);

        $this->model->check($securityEventType);
    }

    /**
     * @return array
     */
    public function dataProviderSecurityEventTypeWithRequestsMethod()
    {
        return [
            [
                \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST,
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP_AND_EMAIL
            ],
            [
                \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST,
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP
            ],
            [
                \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST,
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_EMAIL
            ],
            [
                \Magento\Security\Model\PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP_AND_EMAIL
            ],
            [
                \Magento\Security\Model\PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP
            ],
            [
                \Magento\Security\Model\PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_EMAIL
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
            ->will($this->returnValue(12345));

        $this->securityConfigMock->expects($this->any())
            ->method('getPasswordResetProtectionType')
            ->will($this->returnValue($requestsMethod));

        $this->securityConfigMock->expects($this->once())
            ->method('getMinTimeBetweenPasswordResetRequests')
            ->will($this->returnValue($limitTimeBetweenPasswordResetRequests));

        $this->securityConfigMock->expects($this->any())
            ->method('getCustomerServiceEmail')
            ->will($this->returnValue('test@host.com'));

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
