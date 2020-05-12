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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
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
}
