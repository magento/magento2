<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Controller\Adminhtml;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\Config as Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\User\Model\User as UserModel;
use Magento\User\Test\Fixture\User as UserDataFixture;
use Magento\User\Model\UserFactory;

/**
 * Test class for user reset password email
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 */
class UserResetPasswordEmailTest extends AbstractBackendController
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var \Magento\Framework\Mail\MessageInterfaceFactory
     */
    private $messageFactory;

    /**
     * @var \Magento\Framework\Mail\TransportInterfaceFactory
     */
    private $transportFactory;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->userModel = $this->_objectManager->create(UserModel::class);
        $this->messageFactory = $this->_objectManager->get(\Magento\Framework\Mail\MessageInterfaceFactory::class);
        $this->transportFactory = $this->_objectManager->get(\Magento\Framework\Mail\TransportInterfaceFactory::class);
        $this->userFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(UserFactory::class);
    }

    #[
        Config('admin/emails/forgot_email_template', 'admin_emails_forgot_email_template'),
        Config('admin/emails/forgot_email_identity', 'general'),
        Config('web/url/use_store', 1),
        DataFixture(UserDataFixture::class, ['role_id' => 1], 'user')
    ]
    public function testUserResetPasswordEmail()
    {
        $user = $this->fixtures->get('user');
        $userEmail = $user->getDataByKey('email');
        $transportMock = $this->_objectManager->get(TransportBuilderMock::class);
        $this->getRequest()->setPostValue('email', $userEmail);
        $this->dispatch('backend/admin/auth/forgotpassword');
        $message = $transportMock->getSentMessage();
        $this->assertNotEmpty($message);
        $this->assertEquals('backend/admin/auth/resetpassword', $this->getResetPasswordUri($message));
    }

    private function getResetPasswordUri(EmailMessage $message): string
    {
        $store = $this->_objectManager->get(Store::class);
        $emailParts = $message->getBody()->getParts();
        $messageContent = current($emailParts)->getRawContent();
        $pattern = '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
        preg_match_all($pattern, $messageContent, $match);
        $urlString = trim($match[0][0], $store->getBaseUrl('web'));
        return substr($urlString, 0, strpos($urlString, "/key"));
    }

    /**
     * Test admin email notification after password change
     * @throws LocalizedException
     * @return void
     */
    #[
        DataFixture(UserDataFixture::class, ['role_id' => 1], 'user')
    ]
    public function testAdminEmailNotificationAfterPasswordChange() :void
    {
        // Load admin user
        $user = $this->fixtures->get('user');
        $username = $user->getDataByKey('username');
        $adminEmail = $user->getDataByKey('email');

        // login with old credentials
        $adminUser = $this->userFactory->create();
        $adminUser->login($username, \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD);

        // Change password
        $adminUser->setPassword('newPassword123');
        $adminUser->save();

        /** @var TransportBuilder $transportBuilder */
        $transportBuilder = $this->_objectManager->get(TransportBuilder::class);
        $transport = $transportBuilder->setTemplateIdentifier('customer_account_information_change_email_and_password_template')
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
            ->setTemplateVars(['customer' => $adminUser])
            ->addTo($adminEmail)
            ->getTransport();
        $transport->sendMessage();
        $sentMessage = $transport->getMessage();

        $this->assertEquals('Your Default email and password has been changed', $sentMessage->getSubject());
        $this->assertNotNull($transport->getMessage());

        $messageRaw = $sentMessage->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString(
            'We have received a request to change the following information associated with your account',
            $messageRaw
        );
    }
}
