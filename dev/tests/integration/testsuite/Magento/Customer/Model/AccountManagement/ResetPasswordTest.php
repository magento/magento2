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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
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
                    1,
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

        $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET, 1);
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
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testSendPasswordResetLinkBadEmailOrWebsite(): void
    {
        $email = 'foo@example.com';

        try {
            $this->accountManagement->initiatePasswordReset(
                $email,
                AccountManagement::EMAIL_RESET,
                0
            );
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'email',
                'fieldValue' => $email,
                'field2Name' => 'websiteId',
                'field2Value' => 0,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testSendPasswordResetLinkBadEmailDefaultWebsite(): void
    {
        $email = 'foo@example.com';

        try {
            $this->accountManagement->initiatePasswordReset(
                $email,
                AccountManagement::EMAIL_RESET
            );
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            // App area is frontend, so we expect websiteId of 1.
            $this->assertEquals('No such entity with email = foo@example.com, websiteId = 1', $nsee->getMessage());
        }
    }
}
