<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Controller\Adminhtml;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessage;
use Magento\Store\Model\Store;
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

/**
 * Test class for user reset password email
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
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->userModel = $this->_objectManager->create(UserModel::class);
        $this->userFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(UserFactory::class);
        $this->configWriter = $this->_objectManager->get(WriterInterface::class);
        $this->resourceConnection = $this->_objectManager->get(ResourceConnection::class);
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
     * @return void
     * @throws LocalizedException
     */
    #[
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

        // Setting Password Reset Protection Type to By Email
        $this->configWriter->save('admin/security/password_reset_protection_type', 3);

        // Setting Max Number of Password Reset Requests 0
        $this->configWriter->save('admin/security/max_number_password_reset_requests', 0);

        // Setting Min Time Between Password Reset Requests 0
        $this->configWriter->save('admin/security/min_time_between_password_reset_requests', 0);

        // Resetting Password
        $this->getRequest()->setPostValue('email', $adminEmail);
        $this->dispatch('backend/admin/auth/forgotpassword');

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
        $this->configWriter->save('admin/security/max_number_password_reset_requests', 3);

        // Resetting password multiple times
        for ($i = 0; $i < 1; $i++) {
            $this->getRequest()->setPostValue('email', $adminEmail);
            $this->dispatch('backend/admin/auth/forgotpassword');

            $this->assertSessionMessages(
                $this->equalTo(
                    ['We received too many requests for password resets.'
                    . ' Please wait and try again later or contact hello@example.com.']
                ),
                MessageInterface::TYPE_ERROR
            );
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('password_reset_request_event');

        $connection->truncateTable($tableName);

        $this->assertEquals(0, $connection->fetchOne("SELECT COUNT(*) FROM $tableName"));

        $sendMessage = $transportMock->getSentMessage()->getBody()->getParts()[0]->getRawContent();

        $this->assertStringContainsString(
            'There was recently a request to change the password for your account',
            $sendMessage
        );
    }
}
