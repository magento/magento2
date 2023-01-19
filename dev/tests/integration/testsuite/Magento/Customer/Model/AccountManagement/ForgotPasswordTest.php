<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Class checks password forgot scenarios
 *
 * @magentoDbIsolation enabled
 */
class ForgotPasswordTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var TransportBuilderMock */
    private $transportBuilder;

    /** @var string */
    private $newPasswordLinkPath = "//a[contains(@href, 'customer/account/createPassword') "
    . "and contains(text(), 'Set a New Password')]";

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testForgotPassword(): void
    {
        $email = 'customer@example.com';
        $result = $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET);
        $message = $this->transportBuilder->getSentMessage();
        $messageContent = $message->getBody()->getParts()[0]->getRawContent();
        $this->assertTrue($result);
        $this->assertEquals(1, Xpath::getElementsCountForXpath($this->newPasswordLinkPath, $messageContent));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testResetPasswordFlowStorefront(): void
    {
        // Forgot password section
        $email = 'customer@example.com';
        $result = $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET);
        $message = $this->transportBuilder->getSentMessage();
        $messageContent = $message->getBody()->getParts()[0]->getRawContent();
        $this->assertTrue($result);
        $this->assertEquals(1, Xpath::getElementsCountForXpath($this->newPasswordLinkPath, $messageContent));

        // Send reset password link
        $websiteId = (int)$this->storeManager->getWebsite('base')->getId();
        $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET, $websiteId);

        // login with old credentials
        $customer = $this->accountManagement->authenticate('customer@example.com', 'password');

        $this->assertEquals(
            $customer->getId(),
            $this->accountManagement->authenticate('customer@example.com', 'password')->getId()
        );

        // Change password
        $this->accountManagement->changePassword('customer@example.com', 'password', 'new_Password123');

        // Login with new credentials
        $this->accountManagement->authenticate('customer@example.com', 'new_Password123');

        $this->assertEquals(
            $customer->getId(),
            $this->accountManagement->authenticate('customer@example.com', 'new_Password123')->getId()
        );
    }
}
