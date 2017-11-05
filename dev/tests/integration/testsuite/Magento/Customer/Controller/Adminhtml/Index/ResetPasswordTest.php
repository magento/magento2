<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

/**
 * ResetPassword controller test.
 *
 * @magentoAppArea adminhtml
 */
class ResetPasswordTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Base controller URL
     *
     * @var string
     */
    protected $baseControllerUrl = 'http://localhost/index.php/backend/customer/index/';

    /**
     * Checks reset password functionality with no restrictive settings and customer reset request event.
     * Admin is not affected by this security check, so reset password email must be sent.
     *
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @magentoConfigFixture current_store customer/password/min_time_between_password_reset_requests 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordSuccess()
    {
        $this->passwordResetRequestEventCreate(
            \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST
        );
        $this->getRequest()->setPostValue(['customer_id' => '1']);
        $this->dispatch('backend/customer/index/resetPassword');
        $this->assertSessionMessages(
            $this->equalTo(['The customer will receive an email with a link to reset password.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl . 'edit'));
    }

    /**
     * Checks reset password functionality with default restrictive min time between
     * password reset requests and customer reset request event.
     * Admin is not affected by this security check, so reset password email must be sent.
     *
     * @magentoConfigFixture current_store customer/password/max_number_password_reset_requests 0
     * @magentoConfigFixture current_store customer/password/min_time_between_password_reset_requests 10
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordMinTimeError()
    {
        $this->passwordResetRequestEventCreate(
            \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST
        );
        $this->getRequest()->setPostValue(['customer_id' => '1']);
        $this->dispatch('backend/customer/index/resetPassword');
        $this->assertSessionMessages(
            $this->equalTo(['The customer will receive an email with a link to reset password.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl . 'edit'));
    }

    /**
     * Checks reset password functionality with default restrictive limited number
     * password reset requests and customer reset request event.
     * Admin is not affected by this security check, so reset password email must be sent.
     *
     * @magentoConfigFixture current_store customer/password/max_number_password_reset_requests 1
     * @magentoConfigFixture current_store customer/password/min_time_between_password_reset_requests 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordLimitError()
    {
        $this->passwordResetRequestEventCreate(
            \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST
        );
        $this->getRequest()->setPostValue(['customer_id' => '1']);
        $this->dispatch('backend/customer/index/resetPassword');
        $this->assertSessionMessages(
            $this->equalTo(['The customer will receive an email with a link to reset password.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl . 'edit'));
    }

    /**
     * Checks reset password functionality with default settings, customer and admin reset request events.
     *
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 1
     * @magentoConfigFixture current_store customer/password/min_time_between_password_reset_requests 10
     * @magentoConfigFixture current_store contact/email/recipient_email hello@example.com
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordWithSecurityViolationException()
    {
        $this->passwordResetRequestEventCreate(
            \Magento\Security\Model\PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST
        );
        $this->passwordResetRequestEventCreate(
            \Magento\Security\Model\PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST
        );
        $this->getRequest()->setPostValue(['customer_id' => '1']);
        $this->dispatch('backend/customer/index/resetPassword');
        $this->assertSessionMessages(
            $this->equalTo(['The customer will receive an email with a link to reset password.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl . 'edit'));
    }

    /**
     * Create and save reset request event with provided request type.
     *
     * @param int $requestType
     */
    private function passwordResetRequestEventCreate($requestType)
    {
        $passwordResetRequestEventFactory = $this->_objectManager->get(
            \Magento\Security\Model\PasswordResetRequestEventFactory::class
        );
        $passwordResetRequestEvent = $passwordResetRequestEventFactory->create();
        $passwordResetRequestEvent
            ->setRequestType($requestType)
            ->setAccountReference('customer@example.com')
            ->setCreatedAt(strtotime('now'))
            ->setIp('3232249856')
            ->save();
    }
}
