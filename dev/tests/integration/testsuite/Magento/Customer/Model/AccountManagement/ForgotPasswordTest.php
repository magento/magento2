<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
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

    /** @var DataFixtureStorage */
    private $fixtures;

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
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
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
     * @return void
     * @throws LocalizedException
     */
    #[
        DataFixture(Customer::class, ['email' => 'customer@search.example.com'], as: 'customer'),
    ]
    public function testResetPasswordFlowStorefront(): void
    {
        // Forgot password section;
        $customer = $this->fixtures->get('customer');
        $email = $customer->getEmail();
        $customerId = (int)$customer->getId();
        $result = $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET);
        $message = $this->transportBuilder->getSentMessage();
        $messageContent = $message->getBody()->getParts()[0]->getRawContent();
        $this->assertTrue($result);
        $this->assertEquals(1, Xpath::getElementsCountForXpath($this->newPasswordLinkPath, $messageContent));

        // Send reset password link
        $defaultWebsiteId = (int)$this->storeManager->getWebsite('base')->getId();
        $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET, $defaultWebsiteId);

        // login with old credentials
        $this->assertEquals(
            $customerId,
            (int)$this->accountManagement->authenticate($email, 'password')->getId()
        );

        // Change password
        $this->accountManagement->changePassword($email, 'password', 'new_Password123');

        // Login with new credentials
        $this->accountManagement->authenticate($email, 'new_Password123');

        $this->assertEquals(
            $customerId,
            $this->accountManagement->authenticate($email, 'new_Password123')->getId()
        );
    }
}
