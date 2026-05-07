<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\ComponentsDir;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Test creating a customer through GraphQL
 *
 * @magentoAppArea graphql
 */
class CreateCustomerTest extends TestCase
{
    /**
     * @var GraphQlRequest
     */
    private $graphQlRequest;

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->graphQlRequest = Bootstrap::getObjectManager()->create(GraphQlRequest::class);
        $this->json = Bootstrap::getObjectManager()->get(SerializerInterface::class);

        $this->customerRepository = Bootstrap::getObjectManager()
            ->create(CustomerRepositoryInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()
            ->create(StoreRepositoryInterface::class);
    }

    /**
     * Get create customer GraphQL mutation
     *
     * @param string $email
     * @return string
     */
    private function getCreateCustomerMutation(string $email): string
    {
        return <<<MUTATION
            mutation createAccount {
                createCustomer(
                    input: {
                        email: "{$email}"
                        firstname: "Test"
                        lastname: "Magento"
                        password: "T3stP4assw0rd"
                        is_subscribed: false
                    }
                ) {
                    customer {
                        email
                    }
                }
            }
        MUTATION;
    }

    /**
     * Assert expected create customer GraphQL response structure
     *
     * @param array $responseData
     * @param string $email
     * @return void
     */
    private function assertCreateCustomerResponse(array $responseData, string $email): void
    {
        $this->assertEquals(
            [
                'data' => [
                    'createCustomer' => [
                        'customer' => [
                            'email' => $email
                        ]
                    ]
                ]
            ],
            $responseData
        );
    }

    /**
     * Assert that customer email was sent
     *
     * @return TransportBuilderMock
     */
    private function assertCustomerEmailSent(): TransportBuilderMock
    {
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = Bootstrap::getObjectManager()
            ->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();

        $this->assertNotNull($sentMessage);

        return $transportBuilderMock;
    }

    /**
     * Generate unique email address for testing
     *
     * @return string
     */
    private function generateUniqueEmail(): string
    {
        return 'test' . uniqid() . '@magento.com';
    }

    /**
     * Return decoded email body text for assertions (Laminas MIME vs Symfony).
     *
     * @param object $sentMessage
     * @return string
     */
    private function getDecodedEmailBody(object $sentMessage): string
    {
        $body = $sentMessage->getBody();
        if (!is_object($body)) {
            return '';
        }
        if (method_exists($body, 'generateMessage')) {
            return quoted_printable_decode($body->generateMessage());
        }
        if (method_exists($body, 'bodyToString')) {
            return quoted_printable_decode($body->bodyToString());
        }
        if (method_exists($body, 'getParts')) {
            $parts = $body->getParts();
            if (isset($parts[0]) && is_object($parts[0]) && method_exists($parts[0], 'getRawContent')) {
                return quoted_printable_decode((string) $parts[0]->getRawContent());
            }
        }

        return '';
    }

    /**
     * Test that creating a customer sends an email
     *
     * @return void
     */
    public function testCreateCustomerSendsEmail(): void
    {
        $email = $this->generateUniqueEmail();

        $response = $this->graphQlRequest->send($this->getCreateCustomerMutation($email));
        $this->assertCreateCustomerResponse(
            $this->json->unserialize($response->getContent()),
            $email
        );

        $customer = $this->customerRepository->get($email);
        $this->assertEquals('Test', $customer->getFirstname());
        $this->assertEquals('Magento', $customer->getLastname());

        $transportBuilderMock = $this->assertCustomerEmailSent();

        $sentMessage = $transportBuilderMock->getSentMessage();
        $this->assertEquals('Test Magento', $sentMessage->getTo()[0]->getName());
        $this->assertEquals($email, $sentMessage->getTo()[0]->getEmail());
        $this->assertEquals('Welcome to Main Website Store', $sentMessage->getSubject());
        $messageBody = $this->getDecodedEmailBody($sentMessage);
        $this->assertStringContainsString('Welcome to Main Website Store.', $messageBody);
    }

    /**
     * Test that creating a customer on an alternative store sends an email
     *
     * @return void
     */
    #[
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, [
            'name' => 'Test Group',
            'website_id' => '$website2.id$'
        ], 'store_group2'),
        DataFixture(StoreFixture::class, [
            'code' => 'test_store_view',
            'name' => 'Test Store View',
            'store_group_id' => '$store_group2.id$'
        ])
    ]
    public function testCreateCustomerForStoreSendsEmail(): void
    {
        $email = $this->generateUniqueEmail();

        $responseData = $this->json->unserialize(
            $this->graphQlRequest->send(
                $this->getCreateCustomerMutation($email),
                [],
                '',
                ['Store' => 'test_store_view']
            )->getContent()
        );

        $this->assertCreateCustomerResponse($responseData, $email);

        $customer = $this->customerRepository->get($email);
        $this->assertEquals('Test', $customer->getFirstname());
        $this->assertEquals('Magento', $customer->getLastname());
        $this->assertEquals('Test Store View', $customer->getCreatedIn());

        $store = $this->storeRepository->getById($customer->getStoreId());
        $this->assertEquals('test_store_view', $store->getCode());

        $transportBuilderMock = $this->assertCustomerEmailSent();

        $sentMessage = $transportBuilderMock->getSentMessage();
        $this->assertEquals('Test Magento', $sentMessage->getTo()[0]->getName());
        $this->assertEquals($email, $sentMessage->getTo()[0]->getEmail());
        $this->assertEquals('Welcome to Test Group', $sentMessage->getSubject());
        $messageBody = $this->getDecodedEmailBody($sentMessage);
        $this->assertStringContainsString('Welcome to Test Group.', $messageBody);
    }

    /**
     * Test that creating a customer on an alternative store sends an email in the translated
     * language
     *
     * @return void
     */
    #[
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(
            StoreGroupFixture::class,
            ['name' => 'Test Group', 'website_id' => '$website2.id$'],
            'store_group2'
        ),
        DataFixture(
            StoreFixture::class,
            [
                'code' => 'test_store_view',
                'name' => 'Test Store View',
                'store_group_id' => '$store_group2.id$'
            ]
        ),
        Config('general/locale/code', 'fr_FR', 'store', 'test_store_view'),
        ComponentsDir('Magento/CustomerGraphQl/_files')
    ]
    public function testCreateCustomerForStoreSendsTranslatedEmail(): void
    {
        $email = $this->generateUniqueEmail();

        $responseData = $this->json->unserialize(
            $this->graphQlRequest->send(
                $this->getCreateCustomerMutation($email),
                [],
                '',
                ['Store' => 'test_store_view']
            )->getContent()
        );

        $this->assertCreateCustomerResponse($responseData, $email);

        $customer = $this->customerRepository->get($email);
        $this->assertEquals('Test', $customer->getFirstname());
        $this->assertEquals('Magento', $customer->getLastname());
        $this->assertEquals('Test Store View', $customer->getCreatedIn());

        $store = $this->storeRepository->getById($customer->getStoreId());
        $this->assertEquals('test_store_view', $store->getCode());

        $transportBuilderMock = $this->assertCustomerEmailSent();

        $sentMessage = $transportBuilderMock->getSentMessage();
        $this->assertEquals('Test Magento', $sentMessage->getTo()[0]->getName());
        $this->assertEquals($email, $sentMessage->getTo()[0]->getEmail());
        $this->assertEquals('Bienvenue sur Test Group', $sentMessage->getSubject());
        $messageBody = $this->getDecodedEmailBody($sentMessage);
        $this->assertStringContainsString('Bienvenue sur Test Group.', $messageBody);
    }
}
