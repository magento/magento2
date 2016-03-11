<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\SecurityChecker;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\SecurityChecker\Quantity testing
 */
class QuantityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Security\Model\SecurityChecker\Quantity
     */
    protected $model;

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfigMock;

    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory
     */
    protected $passwordResetRequestEventCollectionFactoryMock;

    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection
     */
    protected $passwordResetRequestEventCollectionMock;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->securityConfigMock = $this->getMock(
            'Magento\Security\Helper\SecurityConfig',
            [
                'getRemoteIp',
                'getLimitPasswordResetRequestsMethod',
                'getLimitNumberPasswordResetRequests',
                'getCustomerServiceEmail'
            ],
            [],
            '',
            false
        );

        $this->passwordResetRequestEventCollectionFactoryMock = $this->getMock(
            '\Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->passwordResetRequestEventCollectionMock = $this->getMock(
            '\Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection',
            ['addFieldToFilter', 'filterByLifetime', 'count'],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            'Magento\Security\Model\SecurityChecker\Quantity',
            [
                'securityConfig' => $this->securityConfigMock,
                'passwordResetRequestEventCollectionFactory' => $this->passwordResetRequestEventCollectionFactoryMock
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
        $limitNumberPasswordResetRequests = 10;

        $this->prepareTestCheck($requestsMethod, $limitNumberPasswordResetRequests);

        $this->passwordResetRequestEventCollectionMock->expects($this->once())
            ->method('count')
            ->willReturn($limitNumberPasswordResetRequests - 1);

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
        $limitNumberPasswordResetRequests = 10;

        $this->prepareTestCheck($requestsMethod, $limitNumberPasswordResetRequests);

        $this->passwordResetRequestEventCollectionMock->expects($this->once())
            ->method('count')
            ->willReturn($limitNumberPasswordResetRequests);

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
            ]
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Security module: Unknown security event type
     */
    public function testCheckScopeByEventException()
    {
        $securityEventType = 2;
        $this->model->check($securityEventType);
    }

    /**
     * @param int $requestsMethod
     * @param int $limitNumberPasswordResetRequests
     */
    protected function prepareTestCheck($requestsMethod, $limitNumberPasswordResetRequests)
    {
        $this->securityConfigMock->expects($this->any())
            ->method('getRemoteIp')
            ->will($this->returnValue(12345));

        $this->securityConfigMock->expects($this->any())
            ->method('getLimitPasswordResetRequestsMethod')
            ->will($this->returnValue($requestsMethod));

        $this->securityConfigMock->expects($this->once())
            ->method('getLimitNumberPasswordResetRequests')
            ->will($this->returnValue($limitNumberPasswordResetRequests));

        $this->securityConfigMock->expects($this->any())
            ->method('getCustomerServiceEmail')
            ->will($this->returnValue('test@host.com'));

        $this->passwordResetRequestEventCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->passwordResetRequestEventCollectionMock);

        $this->passwordResetRequestEventCollectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $this->passwordResetRequestEventCollectionMock->expects($this->once())
            ->method('filterByLifetime')
            ->willReturnSelf();
    }
}
