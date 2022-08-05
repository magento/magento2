<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;

class SecurityManagerTest extends \PHPUnit\Framework\TestCase
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
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->create(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $this->securityManager = $this->objectManager->create(\Magento\Security\Model\SecurityManager::class);
        $this->passwordResetRequestEvent = $this->objectManager
            ->get(\Magento\Security\Model\PasswordResetRequestEvent::class);
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
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
            \Magento\Security\Model\SecurityManager::class,
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
     * @magentoConfigFixture current_store customer/password/max_number_password_reset_requests 1
     * @magentoConfigFixture current_store customer/password/min_time_between_password_reset_requests 0
     * @magentoConfigFixture current_store contact/email/recipient_email hi@example.com
     * @magentoDbIsolation enabled
     */
    public function testPerformSecurityCheckLimitNumber()
    {
        $this->expectException(\Magento\Framework\Exception\SecurityViolationException::class);

        $attempts = 2;
        $requestType = \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST;
        $longIp = 127001;
        $accountReference = 'customer@example.com';

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

        $this->expectExceptionMessage(
            'We received too many requests for password resets. '
            . 'Please wait and try again later or contact hi@example.com.'
        );
    }

    /**
     * Test for performSecurityCheck() method when time between password reset events is exceeded
     *
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 1
     * @magentoConfigFixture current_store customer/password/max_number_password_reset_requests 0
     * @magentoConfigFixture current_store customer/password/min_time_between_password_reset_requests 1
     * @magentoConfigFixture current_store contact/email/recipient_email hi@example.com
     * @magentoDbIsolation enabled
     */
    public function testPerformSecurityCheckLimitTime()
    {
        $this->expectException(\Magento\Framework\Exception\SecurityViolationException::class);

        $attempts = 2;
        $requestType = \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST;
        $longIp = 127001;
        $accountReference = 'customer@example.com';

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

        $this->fail('Something went wrong. Please check method execution logic.');

        $this->expectExceptionMessage(
            'We received too many requests for password resets. '
            . 'Please wait and try again later or contact hi@example.com.'
        );
    }
}
