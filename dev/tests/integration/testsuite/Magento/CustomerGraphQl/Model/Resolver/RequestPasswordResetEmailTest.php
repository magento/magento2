<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\ComponentsDir;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RequestPasswordResetEmailTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GraphQlRequest
     */
    private $graphQlRequest;

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->storeRepository = $this->objectManager->create(StoreRepositoryInterface::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->transportBuilder = $this->objectManager->get(TransportBuilder::class);
    }

    /**
     * Test the consistency of the reset password email for customers registered with a custom store,
     * ensuring alignment between content and subject in translated languages.
     *
     * @dataProvider customerOnFrenchStore
     * @param string $requestFromStore
     * @param string $subject
     * @param string $message
     * @throws NoSuchEntityException
     */
    #[
        AppArea('graphql'),
        Config('web/url/use_store', 1),
        DataFixture(StoreFixture::class, ['code' => 'fr_store_view'], 'store2'),
        Config('general/locale/code', 'fr_FR', ScopeInterface::SCOPE_STORE, 'fr_store_view'),
        ComponentsDir('Magento/CustomerGraphQl/_files'),
        DataFixture(Customer::class, ['email' => 'customer@example.com', 'store_id' => '$store2.id$'], as: 'customer'),
    ]
    public function testResetPasswordEmailRequestFromCustomStoreWhenCustomerIsOnCustomStore(
        string $requestFromStore,
        string $subject,
        string $message
    ) {
        $this->assertResetPasswordEmailContent($requestFromStore, $subject, $message);
    }

    /**
     * Test the consistency of the reset password email for customers registered with a custom store,
     * ensuring alignment between content and subject in translated languages.
     *
     * @dataProvider customerOnDefaultStore
     * @param string $requestFromStore
     * @param string $subject
     * @param string $comment
     * @throws NoSuchEntityException
     */
    #[
        AppArea('graphql'),
        Config('web/url/use_store', 1),
        DataFixture(StoreFixture::class, ['code' => 'fr_store_view'], 'store2'),
        Config('general/locale/code', 'fr_FR', 'store', 'fr_store_view'),
        ComponentsDir('Magento/CustomerGraphQl/_files'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
    ]
    public function testResetPasswordEmailRequestFromCustomStoreWhenCustomerIsOnDefaultStore(
        string $requestFromStore,
        string $subject,
        string $comment
    ) {
        $this->assertResetPasswordEmailContent($requestFromStore, $subject, $comment);
    }

    /**
     * Assert the consistency between the reset password email subject and content
     * when the request originates from different stores.
     *
     * @param string $store
     * @param string $subject
     * @param string $message
     * @throws NoSuchEntityException
     */
    private function assertResetPasswordEmailContent(string $store, string $subject, string $message)
    {
        $customer = $this->fixtures->get('customer');
        $email = $customer->getEmail();

        $query =
            <<<QUERY
mutation {
  requestPasswordResetEmail(email: "{$email}")
}
QUERY;
        $response = $this->graphQlRequest->send(
            $query,
            [],
            '',
            [
                'Store' => $store
            ]
        );
        $response = $this->json->unserialize($response->getContent());
        $this->assertArrayHasKey('requestPasswordResetEmail', $response['data']);
        $this->assertTrue($response['data']['requestPasswordResetEmail']);

        $sentMessage = $this->transportBuilder->getSentMessage();
        // Verify an email was dispatched to the correct user
        $this->assertNotNull($sentMessage);
        $this->assertEquals($customer->getName(), $sentMessage->getTo()[0]->getName());
        $this->assertEquals($customer->getEmail(), $sentMessage->getTo()[0]->getEmail());

        // Assert the email contains the expected content
        $subject = __($subject, $this->storeRepository->getById($customer->getStoreId())->getFrontendName())->render();
        $message = __($message)->render();
        $this->assertEquals($subject, $sentMessage->getSubject());
        $messageRaw = quoted_printable_decode($sentMessage->getBody()->bodyToString());
        $this->assertStringContainsString($message, $messageRaw);
    }

    /**
     * Variations when customer is registered from custom (french) store
     *
     * @return array
     */
    public static function customerOnFrenchStore(): array
    {
        return [
            'request_from_default_store' => [
                'requestFromStore' => 'default',
                'subject' => 'Réinitialiser votre mot de passe %1',
                'comment' => 'Si vous êtes bien à l’origine de cette demande, veuillez cliquer ci-dessous pour' .
                    ' définir un nouveau mot de passe',
            ],
            'request_from_french_store' => [
                'requestFromStore' => 'fr_store_view',
                'subject' => 'Réinitialiser votre mot de passe %1',
                'comment' => 'Si vous êtes bien à l’origine de cette demande, veuillez cliquer ci-dessous pour' .
                    ' définir un nouveau mot de passe',
            ]
        ];
    }

    /**
     * Variations when customer is registered from default store
     *
     * @return array
     */
    public static function customerOnDefaultStore(): array
    {
        return [
            'request_from_default_store' => [
                'requestFromStore' => 'default',
                'subject' => 'Reset your %1 password',
                'comment' => 'There was recently a request to change the password for your account'
            ],
            'request_from_french_store' => [
                'requestFromStore' => 'fr_store_view',
                'subject' => 'Reset your %1 password',
                'comment' => 'There was recently a request to change the password for your account'
            ]
        ];
    }
}
