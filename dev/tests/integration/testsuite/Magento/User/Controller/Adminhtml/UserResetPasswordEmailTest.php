<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Controller\Adminhtml;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Message\MessageInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\User\Model\User as UserModel;
use Magento\User\Model\UserFactory;
use Magento\User\Test\Fixture\User as UserDataFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Config\Model\ResourceModel\Config as CoreConfig;

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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var CoreConfig
     */
    protected $resourceConfig;
    
    /**
     * @var \Magento\Framework\Mail\MessageInterfaceFactory
     */
    private $messageFactory;

    /**
     * @var \Magento\Framework\Mail\TransportInterfaceFactory
     */
    private $transportFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->userModel = $this->_objectManager->create(UserModel::class);
        $this->userFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(UserFactory::class);
        $this->resourceConnection = $this->_objectManager->get(ResourceConnection::class);
        $this->reinitableConfig = $this->_objectManager->get(ReinitableConfigInterface::class);
        $this->resourceConfig = $this->_objectManager->get(CoreConfig::class);
        $this->messageFactory = $this->_objectManager->get(\Magento\Framework\Mail\MessageInterfaceFactory::class);
        $this->transportFactory = $this->_objectManager->get(\Magento\Framework\Mail\TransportInterfaceFactory::class);
        $this->configWriter = $this->_objectManager->get(WriterInterface::class);
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
     *
     * @throws LocalizedException
     * @return void
     */
    #[
        DataFixture(UserDataFixture::class, ['role_id' => 1], 'user')
    ]
    public function testAdminEmailNotificationAfterPasswordChange(): void
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

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->_objectManager->get(TransportBuilderMock::class);
        $transportBuilderMock->setTemplateIdentifier(
            'customer_password_reset_password_template'
        )->setTemplateVars([
            'customer' => [
                'name' => $user->getDataByKey('firstname') . ' ' . $user->getDataByKey('lastname')
            ]
        ])->setTemplateOptions([
            'area' => Area::AREA_FRONTEND,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ])
        ->addTo($adminEmail)
        ->getTransport();

        $message = $transportBuilderMock->getSentMessage();

        // Verify an email was dispatched to the correct user
        $this->assertNotNull($transportBuilderMock->getSentMessage());
        $this->assertEquals($adminEmail, $message->getTo()[0]->getEmail());
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    #[
        DbIsolation(false),
        Config(
            'admin/security/min_time_between_password_reset_requests',
            '0',
            'store'
        ),
        DataFixture(UserDataFixture::class, ['role_id' => 1], 'user')
    ]
    public function testEnablePasswordChangeFrequencyLimit(): void
    {
        // Load admin user
        $user = $this->fixtures->get('user');
        $username = $user->getDataByKey('username');
        $adminEmail = $user->getDataByKey('email');

        // login admin
        $adminUser = $this->userFactory->create();
        $adminUser->login($username, \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD);

        // Resetting password multiple times
        for ($i = 0; $i < 5; $i++) {
            $this->getRequest()->setPostValue('email', $adminEmail);
            $this->dispatch('backend/admin/auth/forgotpassword');
        }

        /** @var TransportBuilderMock $transportMock */
        $transportMock = Bootstrap::getObjectManager()->get(
            TransportBuilderMock::class
        );
        $sendMessage = $transportMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();

        $this->assertStringContainsString(
            'There was recently a request to change the password for your account',
            $sendMessage
        );

        // Setting the limit to greater than 0
        $this->configWriter->save('admin/security/min_time_between_password_reset_requests', 2);

        // Resetting password multiple times
        for ($i = 0; $i < 5; $i++) {
            $this->getRequest()->setPostValue('email', $adminEmail);
            $this->dispatch('backend/admin/auth/forgotpassword');
        }

        $this->assertSessionMessages(
            $this->equalTo(
                ['We received too many requests for password resets.'
                . ' Please wait and try again later or contact hello@example.com.']
            ),
            MessageInterface::TYPE_ERROR
        );

        // Wait for 2 minutes before resetting password
        sleep(120);

        $this->getRequest()->setPostValue('email', $adminEmail);
        $this->dispatch('backend/admin/auth/forgotpassword');

        $sendMessage = $transportMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString(
            'There was recently a request to change the password for your account',
            $sendMessage
        );
    }
    
    /**
     * @return void
     * @throws LocalizedException
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(UserDataFixture::class, ['role_id' => 1], 'user')
    ]
    public function testLimitNumberOfResetRequestPerHourByEmail(): void
    {
        // Load admin user
        $user = $this->fixtures->get('user');
        $username = $user->getDataByKey('username');
        $adminEmail = $user->getDataByKey('email');

        // login admin
        $adminUser = $this->userFactory->create();
        $adminUser->login($username, \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD);

        // Setting Password Reset Protection Type By Email
        $this->resourceConfig->saveConfig(
            'admin/security/password_reset_protection_type',
            3,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        // Setting Max Number of Password Reset Requests 0
        $this->resourceConfig->saveConfig(
            'admin/security/max_number_password_reset_requests',
            0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        // Setting Min Time Between Password Reset Requests 0
        $this->resourceConfig->saveConfig(
            'admin/security/min_time_between_password_reset_requests',
            0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $this->reinitableConfig->reinit();

        // Resetting Password
        $this->resetPassword($adminEmail);

        /** @var TransportBuilderMock $transportMock */
        $transportMock = Bootstrap::getObjectManager()->get(
            TransportBuilderMock::class
        );
        $sendMessage = $transportMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();

        $this->assertStringContainsString(
            'There was recently a request to change the password for your account',
            $sendMessage
        );

        $this->assertSessionMessages(
            $this->equalTo([]),
            MessageInterface::TYPE_ERROR
        );

        // Setting Max Number of Password Reset Requests greater than 0
        $this->resourceConfig->saveConfig(
            'admin/security/max_number_password_reset_requests',
            2,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $this->reinitableConfig->reinit();

        $this->resetPassword($adminEmail);
        $this->assertSessionMessages(
            $this->equalTo([]),
            MessageInterface::TYPE_ERROR
        );

        // Resetting password multiple times
        for ($i = 0; $i < 2; $i++) {
            $this->resetPassword($adminEmail);
            $this->assertSessionMessages(
                $this->equalTo(
                    ['We received too many requests for password resets.'
                    . ' Please wait and try again later or contact hello@example.com.']
                ),
                MessageInterface::TYPE_ERROR
            );
        }

        // Clearing the table password_reset_request_event
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('password_reset_request_event');
        $connection->truncateTable($tableName);

        $this->assertEquals(0, $connection->fetchOne("SELECT COUNT(*) FROM $tableName"));

        $this->resetPassword($adminEmail);
        $sendMessage = $transportMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString(
            'There was recently a request to change the password for your account',
            $sendMessage
        );
    }

    /**
     * @param $adminEmail
     * @return void
     */
    private function resetPassword($adminEmail): void
    {
        $this->getRequest()->setPostValue('email', $adminEmail);
        $this->dispatch('backend/admin/auth/forgotpassword');
    }
}
