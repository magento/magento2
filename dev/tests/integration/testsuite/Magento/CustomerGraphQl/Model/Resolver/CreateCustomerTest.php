<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Store\Api\StoreRepositoryInterface;
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
        $messageRaw = $sentMessage->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString('Welcome to Main Website Store.', $messageRaw);
    }


    /**
     * Test that creating a customer on an alternative store sends an email
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
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
                'Store' => 'fixture_second_store'
            ]
        );
        $responseData = $this->json->unserialize($response->getContent());

        // Assert the response of the GraphQL request
        $this->assertNull($responseData['data']['createCustomer']['customer']['id']);

        // Verify the customer was created and has the correct data
        $customer = $this->customerRepository->get('test@magento.com');
        $this->assertEquals('Test', $customer->getFirstname());
        $this->assertEquals('Magento', $customer->getLastname());
        $this->assertEquals('Fixture Second Store', $customer->getCreatedIn());

        $store = $this->storeRepository->getById($customer->getStoreId());
        $this->assertEquals('fixture_second_store', $store->getCode());

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();

        // Verify an email was dispatched to the correct user
        $this->assertNotNull($sentMessage);
        $this->assertEquals('Test Magento', $sentMessage->getTo()[0]->getName());
        $this->assertEquals('test@magento.com', $sentMessage->getTo()[0]->getEmail());

        // Assert the email contains the expected content
        $this->assertEquals('Welcome to second store group', $sentMessage->getSubject());
        $messageRaw = $sentMessage->getBody()->getParts()[0]->getRawContent();
        $this->assertStringContainsString('Welcome to second store group.', $messageRaw);
    }
}
