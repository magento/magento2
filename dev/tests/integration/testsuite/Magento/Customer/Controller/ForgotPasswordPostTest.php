<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller;

use Magento\Config\Model\ResourceModel\Config as CoreConfig;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Theme\Controller\Result\MessagePlugin;

/**
 * Class checks password forgot scenarios
 *
 * @see \Magento\Customer\Controller\Account\ForgotPasswordPost
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPasswordPostTest extends AbstractController
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var TransportBuilderMock */
    private $transportBuilderMock;

    /**
     * @var CoreConfig
     */
    protected $resourceConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $this->resourceConfig = $this->_objectManager->get(CoreConfig::class);
        $this->scopeConfig = Bootstrap::getObjectManager()->get(ScopeConfigInterface::class);
    }

    /**
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @return void
     */
    public function testWithoutEmail(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['email' => '']);
        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('Please enter your email.')]),
            MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
    }

    /**
     * Test that forgot password email message displays special characters correctly.
     *
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/password/forgot_email_template customer_password_forgot_email_template
     * @codingStandardsIgnoreEnd
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoConfigFixture current_store general/store_information/name Test special' characters
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testForgotPasswordEmailMessageWithSpecialCharacters(): void
    {
        $email = 'customer@example.com';
        $this->getRequest()->setPostValue(['email' => $email]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSuccessSessionMessage($email);
        $subject = $this->transportBuilderMock->getSentMessage()->getSubject();
        $this->assertStringContainsString('Test special\' characters', $subject);
    }

    /**
     * @magentoConfigFixture current_store customer/password/limit_password_reset_requests_method 0
     * @codingStandardsIgnoreStart
     * @magentoConfigFixture current_store customer/password/forgot_email_template customer_password_forgot_email_template
     * @codingStandardsIgnoreEnd
     * @magentoConfigFixture current_store customer/password/forgot_email_identity support
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testForgotPasswordPostAction(): void
    {
        $email = 'customer@example.com';
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['email' => $email]);
        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/'));
        $this->assertSuccessSessionMessage($email);
    }

    /**
     * @magentoConfigFixture current_store customer/captcha/enable 0
     *
     * @return void
     */
    public function testForgotPasswordPostWithBadEmailAction(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['email' => 'bad@email']);
        $this->dispatch('customer/account/forgotPasswordPost');
        $this->assertRedirect($this->stringContains('customer/account/forgotpassword'));
        $this->assertSessionMessages(
            $this->equalTo(['The email address is incorrect. Verify the email address and try again.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Assert success session message
     *
     * @param string $email
     * @return void
     */
    private function assertSuccessSessionMessage(string $email): void
    {
        $message = __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $email
        );
        $this->assertSessionMessages($this->equalTo([$message]), MessageInterface::TYPE_SUCCESS);
    }

    /**
     * Test to enable password change frequency limit for customer
     *
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store customer/password/min_time_between_password_reset_requests 0
     * @magentoConfigFixture current_store customer/captcha/enable 0
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     * @throws LocalizedException
     */
    public function testEnablePasswordChangeFrequencyLimitForCustomer(): void
    {
        $email = 'customer@example.com';

        // Resetting password multiple times
        for ($i = 0; $i < 5; $i++) {
            $this->getRequest()->setPostValue(['email' => $email]);
            $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
            $this->dispatch('customer/account/forgotPasswordPost');
        }

        // Asserting mail received after forgot password
        $sendMessage = $this->transportBuilderMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString(
            'There was recently a request to change the password for your account',
            $sendMessage
        );

        // Updating the limit to greater than 0
        $this->resourceConfig->saveConfig(
            'customer/password/min_time_between_password_reset_requests',
            2,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        // Resetting password multiple times
        for ($i = 0; $i < 5; $i++) {
            $this->clearCookieMessagesList();
            $this->getRequest()->setPostValue('email', $email);
            $this->dispatch('customer/account/forgotPasswordPost');
        }

        // Asserting the error message
        $this->assertSessionMessages(
            $this->equalTo(
                ['We received too many requests for password resets.'
                    . ' Please wait and try again later or contact hello@example.com.']
            ),
            MessageInterface::TYPE_ERROR
        );

        // Wait for 2 minutes before resetting password
        sleep(120);

        // Clicking on the forgot password link
        $this->getRequest()->setPostValue('email', $email);
        $this->dispatch('customer/account/forgotPasswordPost');

        // Asserting mail received after forgot password
        $sendMessage = $this->transportBuilderMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString(
            'There was recently a request to change the password for your account',
            $sendMessage
        );
    }

    /**
     * Clear cookie messages list.
     *
     * @return void
     */
    private function clearCookieMessagesList(): void
    {
        $cookieManager = $this->_objectManager->get(CookieManagerInterface::class);
        $jsonSerializer = $this->_objectManager->get(Json::class);
        $cookieManager->setPublicCookie(
            MessagePlugin::MESSAGES_COOKIES_NAME,
            $jsonSerializer->serialize([])
        );
    }
}
