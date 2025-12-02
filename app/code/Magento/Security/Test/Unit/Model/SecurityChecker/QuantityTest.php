<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\SecurityChecker;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory;
use Magento\Security\Model\SecurityChecker\Quantity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\SecurityChecker\Quantity testing
 */
class QuantityTest extends TestCase
{
    /**
     * @var  Quantity
     */
    protected $model;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $securityConfigMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $collectionMock;

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
        $this->securityConfigMock = $this->createMock(ConfigInterface::class);

        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->collectionMock = $this->createPartialMock(
            Collection::class,
            ['addFieldToFilter', 'filterByLifetime', 'count']
        );

        $this->remoteAddressMock = $this->createMock(RemoteAddress::class);

        $this->model = $this->objectManager->getObject(
            Quantity::class,
            [
                'securityConfig' => $this->securityConfigMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'remoteAddress' => $this->remoteAddressMock
            ]
        );
    }

    /**
     * @param int $securityEventType
     * @param int $requestsMethod
     */
    #[DataProvider('dataProviderSecurityEventTypeWithRequestsMethod')]
    public function testCheck($securityEventType, $requestsMethod)
    {
        $limitNumberPasswordResetRequests = 10;

        $this->prepareTestCheck($requestsMethod, $limitNumberPasswordResetRequests);

        $this->collectionMock->expects($this->once())
            ->method('count')
            ->willReturn($limitNumberPasswordResetRequests - 1);

        $this->model->check($securityEventType);
    }

    /**
     * @param int $securityEventType
     * @param int $requestsMethod
     */
    #[DataProvider('dataProviderSecurityEventTypeWithRequestsMethod')]
    public function testCheckException($securityEventType, $requestsMethod)
    {
        $this->expectException('Magento\Framework\Exception\SecurityViolationException');
        $limitNumberPasswordResetRequests = 10;

        $this->prepareTestCheck($requestsMethod, $limitNumberPasswordResetRequests);

        $this->collectionMock->expects($this->once())
            ->method('count')
            ->willReturn($limitNumberPasswordResetRequests);

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
            ]
        ];
    }

    /**
     * @param int $requestsMethod
     * @param int $limitNumberPasswordResetRequests
     */
    protected function prepareTestCheck($requestsMethod, $limitNumberPasswordResetRequests)
    {
        $this->remoteAddressMock->expects($this->any())
            ->method('getRemoteAddress')
            ->willReturn(12345);

        $this->securityConfigMock->expects($this->any())
            ->method('getPasswordResetProtectionType')
            ->willReturn($requestsMethod);

        $this->securityConfigMock->expects($this->once())
            ->method('getMaxNumberPasswordResetRequests')
            ->willReturn($limitNumberPasswordResetRequests);

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
            ->method('filterByLifetime')
            ->willReturnSelf();
    }
}
