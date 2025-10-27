<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);

        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->storeRepository = $this->objectManager->create(StoreRepositoryInterface::class);
    }

    /**
     * Test that creating a customer sends an email
     */
    public function testCreateCustomerSendsEmail()
    {
        $query
            = <<<QUERY
mutation createAccount {
    createCustomer(
        input: {
            email: "test@magento.com"
            firstname: "Test"
            lastname: "Magento"
            password: "T3stP4assw0rd"
            is_subscribed: false
        }
    ) {
        customer {
            id
        }
    }
}
QUERY;

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());

        // Assert the response of the GraphQL request
        $this->assertNull($responseData['data']['createCustomer']['customer']['id']);

        // Verify the customer was created and has the correct data
        $customer = $this->customerRepository->get('test@magento.com');
        $this->assertEquals('Test', $customer->getFirstname());
        $this->assertEquals('Magento', $customer->getLastname());

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();

        // Verify an email was dispatched to the correct user
        $this->assertNotNull($sentMessage);
        $this->assertEquals('Test Magento', $sentMessage->getTo()[0]->getName());
        $this->assertEquals('test@magento.com', $sentMessage->getTo()[0]->getEmail());

        // Assert the email contains the expected content
        $this->assertEquals('Welcome to Main Website Store', $sentMessage->getSubject());
        $messageRaw = quoted_printable_decode($sentMessage->getBody()->bodyToString());
        $this->assertStringContainsString('Welcome to Main Website Store.', $messageRaw);
    }

    /**
     * Test that creating a customer on an alternative store sends an email
     *
     * @magentoDataFixture Magento/CustomerGraphQl/_files/website_store_with_store_view.php
     */
    public function testCreateCustomerForStoreSendsEmail()
    {
        $query
            = <<<QUERY
mutation createAccount {
    createCustomer(
        input: {
            email: "test@magento.com"
            firstname: "Test"
            lastname: "Magento"
            password: "T3stP4assw0rd"
            is_subscribed: false
        }
    ) {
        customer {
            id
        }
    }
}
QUERY;

        $response = $this->graphQlRequest->send(
            $query,
            [],
            '',
            [
                'Store' => 'test_store_view'
            ]
        );
        $responseData = $this->json->unserialize($response->getContent());

        // Assert the response of the GraphQL request
        $this->assertNull($responseData['data']['createCustomer']['customer']['id']);

        // Verify the customer was created and has the correct data
        $customer = $this->customerRepository->get('test@magento.com');
        $this->assertEquals('Test', $customer->getFirstname());
        $this->assertEquals('Magento', $customer->getLastname());
        $this->assertEquals('Test Store View', $customer->getCreatedIn());

        $store = $this->storeRepository->getById($customer->getStoreId());
        $this->assertEquals('test_store_view', $store->getCode());

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();

        // Verify an email was dispatched to the correct user
        $this->assertNotNull($sentMessage);
        $this->assertEquals('Test Magento', $sentMessage->getTo()[0]->getName());
        $this->assertEquals('test@magento.com', $sentMessage->getTo()[0]->getEmail());

        // Assert the email contains the expected content
        $this->assertEquals('Welcome to Test Group', $sentMessage->getSubject());
        $messageRaw = quoted_printable_decode($sentMessage->getBody()->bodyToString());
        $this->assertStringContainsString('Welcome to Test Group.', $messageRaw);
    }

    /**
     * Test that creating a customer on an alternative store sends an email in the translated language
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
            ['code' => 'test_store_view', 'name' => 'Test Store View', 'store_group_id' => '$store_group2.id$']
        ),
        Config('general/locale/code', 'fr_FR', 'store', 'test_store_view'),
        ComponentsDir('Magento/CustomerGraphQl/_files')
    ]
    public function testCreateCustomerForStoreSendsTranslatedEmail()
    {
        $query
            = <<<QUERY
mutation createAccount {
    createCustomer(
        input: {
            email: "test@magento.com"
            firstname: "Test"
            lastname: "Magento"
            password: "T3stP4assw0rd"
            is_subscribed: false
        }
    ) {
        customer {
            id
        }
    }
}
QUERY;

        $response = $this->graphQlRequest->send(
            $query,
            [],
            '',
            [
                'Store' => 'test_store_view'
            ]
        );
        $responseData = $this->json->unserialize($response->getContent());

        // Assert the response of the GraphQL request
        $this->assertNull($responseData['data']['createCustomer']['customer']['id']);

        // Verify the customer was created and has the correct data
        $customer = $this->customerRepository->get('test@magento.com');
        $this->assertEquals('Test', $customer->getFirstname());
        $this->assertEquals('Magento', $customer->getLastname());
        $this->assertEquals('Test Store View', $customer->getCreatedIn());

        $store = $this->storeRepository->getById($customer->getStoreId());
        $this->assertEquals('test_store_view', $store->getCode());

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();

        // Verify an email was dispatched to the correct user
        $this->assertNotNull($sentMessage);
        $this->assertEquals('Test Magento', $sentMessage->getTo()[0]->getName());
        $this->assertEquals('test@magento.com', $sentMessage->getTo()[0]->getEmail());

        // Assert the email contains the expected content
        $this->assertEquals('Bienvenue sur Test Group', $sentMessage->getSubject());
        $messageRaw = quoted_printable_decode($sentMessage->getBody()->bodyToString());
        $this->assertStringContainsString('Bienvenue sur Test Group.', $messageRaw);
    }
}
