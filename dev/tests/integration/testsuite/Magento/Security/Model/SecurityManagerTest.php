<?php

namespace Magento\Security\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SecurityManagerTest
 * @package Magento\Security\Model
 */
class SecurityManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Security\Model\SecurityManager
     */
    protected $securityManager;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Security\Model\PasswordResetRequestEvent
     */
    protected $passwordResetRequestEvent;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->create('Magento\Customer\Api\AccountManagementInterface');
        $this->securityManager = $this->objectManager->create('Magento\Security\Model\SecurityManager');
        $this->passwordResetRequestEvent = $this->objectManager
            ->get('Magento\Security\Model\PasswordResetRequestEvent');
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $this->objectManager = null;
        $this->accountManagement  = null;
        $this->securityManager  = null;
        parent::tearDown();
    }

    /**
     * Test for performSecurityCheck() method
     *
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @magentoDbIsolation enabled
     */
    public function testPerformSecurityCheck()
    {
        $collection = $this->getPasswordResetRequestEventCollection();
        $sizeBefore = $collection->getSize();

        $requestType = \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST;
        $longIp = 127001;
        $accountReference = 'customer@example.com';
        $this->assertInstanceOf(
            'Magento\Security\Model\SecurityManager',
            $this->securityManager->performSecurityCheck(
                $requestType,
                $accountReference,
                $longIp
            )
        );

        $collection = $this->getPasswordResetRequestEventCollection();
        $sizeAfter = $collection->getSize();
        $this->assertEquals(1, $sizeAfter - $sizeBefore);
    }

    /**
     * Get PasswordResetRequestEvent collection
     *
     * @return \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection
     */
    protected function getPasswordResetRequestEventCollection()
    {
        $collection = $this->passwordResetRequestEvent->getResourceCollection();
        $collection->load();

        return $collection;
    }

    /**
     * Test for performSecurityCheck() method when number of password reset events is exceeded
     *
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 1
     * @magentoConfigFixture current_store customer/password/limit_number_password_reset_requests 1
     * @magentoConfigFixture current_store customer/password/limit_time_between_password_reset_requests 0
     * @magentoConfigFixture current_store contact/email/recipient_email hi@example.com
     * @expectedException \Magento\Framework\Exception\SecurityViolationException
     * @expectedExceptionMessage Too many password reset requests. Please wait and try again or contact hi@example.com.
     * @magentoDbIsolation enabled
     */
    public function testPerformSecurityCheckLimitNumber()
    {
        $attempts = 2;
        $requestType = \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST;
        $longIp = 127001;
        $accountReference = 'customer@example.com';

        $i = 0;
        try {
            for ($i = 0; $i < $attempts; $i++) {
                $this->securityManager->performSecurityCheck($requestType, $accountReference, $longIp);
            }
        } catch (\Magento\Framework\Exception\SecurityViolationException $e) {
            $this->assertEquals(1, $i);
            throw new \Magento\Framework\Exception\SecurityViolationException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Test for performSecurityCheck() method when time between password reset events is exceeded
     *
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 1
     * @magentoConfigFixture current_store customer/password/limit_number_password_reset_requests 0
     * @magentoConfigFixture current_store customer/password/limit_time_between_password_reset_requests 1
     * @magentoConfigFixture current_store contact/email/recipient_email hi@example.com
     * @expectedException \Magento\Framework\Exception\SecurityViolationException
     * @expectedExceptionMessage Too many password reset requests. Please wait and try again or contact hi@example.com.
     * @magentoDbIsolation enabled
     */
    public function testPerformSecurityCheckLimitTime()
    {
        $attempts = 2;
        $requestType = \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST;
        $longIp = 127001;
        $accountReference = 'customer@example.com';

        $i = 0;
        try {
            for ($i = 0; $i < $attempts; $i++) {
                $this->securityManager->performSecurityCheck($requestType, $accountReference, $longIp);
            }
        } catch (\Magento\Framework\Exception\SecurityViolationException $e) {
            $this->assertEquals(1, $i);
            throw new \Magento\Framework\Exception\SecurityViolationException(
                __($e->getMessage())
            );
        }

        $this->assertEquals(1, $i);
    }

    /**
     * Here we test if admin identity check executed successfully
     *
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testAdminIdentityCheck()
    {
        /** @var $user \Magento\User\Model\User */
        $user = $this->objectManager->create('Magento\User\Model\User')->loadByUsername('adminUser');
        $passwordString = \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD;
        $this->securityManager->adminIdentityCheck($user, $passwordString);
    }

    /**
     * Here we check for a wrong password
     *
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     * @expectedException \Magento\Framework\Exception\AuthenticationException
     * @expectedExceptionMessage You have entered an invalid password for current user.
     */
    public function testAdminIdentityCheckWrongPassword()
    {
        /** @var $user \Magento\User\Model\User */
        $user = $this->objectManager->create('Magento\User\Model\User')->loadByUsername('adminUser');
        $passwordString = 'wrongPassword';
        $this->securityManager->adminIdentityCheck($user, $passwordString);
    }

    /**
     * Here we check for a locked user
     *
     * @magentoDataFixture Magento/User/_files/locked_users.php
     * @expectedException \Magento\Framework\Exception\State\UserLockedException
     * @expectedExceptionMessage Your account is temporarily disabled.
     */
    public function testAdminIdentityCheckLockExpires()
    {
        /** @var $user \Magento\User\Model\User */
        $user = $this->objectManager->create('Magento\User\Model\User')->loadByUsername('adminUser2');
        $this->securityManager->adminIdentityCheck($user, \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD);
    }
}
