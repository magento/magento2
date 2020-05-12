<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Tests for customer password reset via customer account management service.
 *
 * @magentoDbIsolation enabled
 */
class ResetPasswordTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var TransportBuilderMock*/
    private $transportBuilderMock;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        parent::setUp();
    }

    /**
     * Assert that when you reset customer password via admin, link with "Set a New Password" is send to customer email.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testSendEmailWithSetNewPasswordLink(): void
    {
        $this->accountManagement->initiatePasswordReset(
            'customer@example.com',
            AccountManagement::EMAIL_REMINDER,
            1
        );
        $customerSecure = $this->customerRegistry->retrieveSecureData(1);
        $mailTemplate = $this->transportBuilderMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();

        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    '//a[contains(@href, \'customer/account/createPassword/?id=%1$d&token=%2$s\')]',
                    $customerSecure->getId(),
                    $customerSecure->getRpToken()
                ),
                $mailTemplate
            ),
            'Reset password creation link was not found.'
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testSendPasswordResetLink(): void
    {
        $email = 'customer@example.com';
        $websiteId = (int)$this->storeManager->getWebsite('base')->getId();

        $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET, $websiteId);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testSendPasswordResetLinkDefaultWebsite(): void
    {
        $email = 'customer@example.com';

        $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET);
    }

    /**
     * @magentoAppArea frontend
     * @dataProvider passwordResetErrorsProvider
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @param string $email
     * @param int|null $websiteId
     * @return void
     */
    public function testPasswordResetErrors(string $email, ?int $websiteId = null): void
    {
        $websiteId = $websiteId ?? (int)$this->storeManager->getWebsite('base')->getId();
        $this->expectExceptionObject(
            NoSuchEntityException::doubleField('email', $email, 'websiteId', $websiteId)
        );
        $this->accountManagement->initiatePasswordReset(
            $email,
            AccountManagement::EMAIL_RESET,
            $websiteId
        );
    }

    /**
     * @return array
     */
    public function passwordResetErrorsProvider(): array
    {
        return [
            'wrong_email' => [
                'email' => 'foo@example.com',
            ],
            'wrong_website_id' => [
                'email' => 'customer@example.com',
                'website_id' => 0,
            ],
        ];
    }
}
