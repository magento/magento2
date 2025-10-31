<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

class ChangePasswordTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var GraphQlRequest
     */
    private $graphQlRequest;

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Test that change password sends an email
     *
     * @magentoAppArea graphql
     * @throws AuthenticationException
     */
    #[
        DbIsolation(false),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
    ]
    public function testChangePasswordSendsEmail(): void
    {
        $currentPassword = 'password';
        $query
            = <<<QUERY
mutation {
  changeCustomerPassword(
    currentPassword: "$currentPassword"
    newPassword: "T3stP4assw0rd"
  ) {
    id
    email
  }
}
QUERY;
        $customer = $this->fixtures->get('customer');
        $response = $this->graphQlRequest->send(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail(), $currentPassword)
        );
        $responseData = $this->json->unserialize($response->getContent());

        // Assert the response of the GraphQL request
        $this->assertNull($responseData['data']['changeCustomerPassword']['id']);
        $this->assertEquals($customer->getEmail(), $responseData['data']['changeCustomerPassword']['email']);

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();

        // Verify an email was dispatched to the correct user
        $this->assertNotNull($sentMessage);
        $this->assertEquals($customer->getName(), $sentMessage->getTo()[0]->getName());
        $this->assertEquals($customer->getEmail(), $sentMessage->getTo()[0]->getEmail());

        // Assert the email contains the expected content
        $this->assertEquals('Your Main Website Store password has been changed', $sentMessage->getSubject());
        $messageRaw = quoted_printable_decode($sentMessage->getBody()->bodyToString());
        $this->assertStringContainsString(
            'We have received a request to change the following information associated with your account',
            $messageRaw
        );
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
