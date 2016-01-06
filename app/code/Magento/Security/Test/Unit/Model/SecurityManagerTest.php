<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\SecurityManager testing
 */
class SecurityManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Security\Model\SecurityManager
     */
    protected $model;

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfigMock;

    /**
     * @var \Magento\Security\Model\PasswordResetRequestEventFactory
     */
    protected $passwordResetRequestEventFactoryMock;

    /**
     * @var \Magento\Security\Model\PasswordResetRequestEvent
     */
    protected $passwordResetRequestEventMock;

    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent
     */
    protected $passwordResetRequestEventResourceMock;

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
            ['getRemoteIp'],
            [],
            '',
            false
        );

        $this->passwordResetRequestEventFactoryMock = $this->getMock(
            '\Magento\Security\Model\PasswordResetRequestEventFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->passwordResetRequestEventMock = $this->getMock(
            '\Magento\Security\Model\PasswordResetRequestEvent',
            ['setRequestType', 'setAccountReference', 'setIp', 'save'],
            [],
            '',
            false
        );

        $this->passwordResetRequestEventResourceMock = $this->getMock(
            '\Magento\Security\Model\ResourceModel\PasswordResetRequestEvent',
            ['deleteRecordsOlderThen'],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            '\Magento\Security\Model\SecurityManager',
            [
                'securityConfig' => $this->securityConfigMock,
                'passwordResetRequestEventModelFactory' => $this->passwordResetRequestEventFactoryMock,
                'passwordResetRequestEventResource' => $this->passwordResetRequestEventResourceMock,
                'securityCheckers' => []
            ]
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

        $this->securityConfigMock->expects($this->any())
            ->method('getRemoteIp')
            ->will($this->returnValue($longIp));

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

        $this->model->performSecurityCheck($requestType, $accountReference, $longIp);
    }

    /**
     * @return void
     */
    public function testCleanExpiredRecords()
    {
        $this->passwordResetRequestEventResourceMock->expects($this->once())
            ->method('deleteRecordsOlderThen')
            ->with(
                $this->securityConfigMock->getCurrentTimestamp()
                - \Magento\Security\Model\SecurityManager::SECURITY_CONTROL_RECORDS_LIFE_TIME
            )
            ->willReturnSelf();

        $this->model->cleanExpiredRecords();
    }
}
