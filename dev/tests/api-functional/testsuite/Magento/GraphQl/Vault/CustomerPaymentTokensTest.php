<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Vault;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\ResourceModel\PaymentToken as TokenResource;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;

/**
 * Tests for customer payment tokens
 */
class CustomerPaymentTokensTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @var CollectionFactory
     */
    private $tokenCollectionFactory;

    /**
     * @var TokenResource
     */
    private $tokenResource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->paymentTokenManagement = Bootstrap::getObjectManager()->get(PaymentTokenManagement::class);
        $this->tokenResource = Bootstrap::getObjectManager()->get(TokenResource::class);
        $this->tokenCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $collection = $this->tokenCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', ['eq' => 1]);

        foreach ($collection->getItems() as $token) {
            // Using the resource directly to delete. Deleting from the repository only makes token inactive
            $this->tokenResource->delete($token);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Vault/_files/payment_tokens.php
     */
    public function testGetCustomerPaymentTokens()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customerPaymentTokens {
        items {
            public_hash
            details
            payment_method_code
            type
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
        $this->assertCount(2, $response['customerPaymentTokens']['items']);
        $this->assertArrayHasKey('public_hash', $response['customerPaymentTokens']['items'][0]);
        $this->assertArrayHasKey('details', $response['customerPaymentTokens']['items'][0]);
        $this->assertArrayHasKey('payment_method_code', $response['customerPaymentTokens']['items'][0]);
        $this->assertArrayHasKey('type', $response['customerPaymentTokens']['items'][0]);
        // Validate gateway token is NOT returned
        $this->assertArrayNotHasKey('gateway_token', $response['customerPaymentTokens']['items'][0]);
        $cartDetails1 = json_decode($response['customerPaymentTokens']['items'][0]['details'], true);
        $cartDetails2 = json_decode($response['customerPaymentTokens']['items'][1]['details'], true);
        $this->assertSame('Visa', $cartDetails1['type']);
        $this->assertSame('American Express', $cartDetails2['cc_type']);
    }

    /**
     */
    public function testGetCustomerPaymentTokensIfUserIsNotAuthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The current customer isn\'t authorized.');

        $query = <<<QUERY
query {
    customerPaymentTokens {
        items {
            public_hash
            details
            payment_method_code
            type
        }
    }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Vault/_files/payment_tokens.php
     */
    public function testDeletePaymentToken()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $tokens = $this->paymentTokenManagement->getVisibleAvailableTokens(1);
        $token = current($tokens);
        $publicHash = $token->getPublicHash();

        $query = <<<QUERY
mutation {
  deletePaymentToken(
    public_hash: "$publicHash"
  ) {
    result
    customerPaymentTokens {
      items {
        public_hash
        details
        payment_method_code
        type
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        $this->assertTrue($response['deletePaymentToken']['result']);
        $this->assertCount(1, $response['deletePaymentToken']['customerPaymentTokens']['items']);

        $token = $response['deletePaymentToken']['customerPaymentTokens']['items'][0];
        $this->assertArrayHasKey('public_hash', $token);
        $this->assertArrayHasKey('details', $token);
        $this->assertArrayHasKey('payment_method_code', $token);
        $this->assertArrayHasKey('type', $token);
        // Validate gateway token is NOT returned
        $this->assertArrayNotHasKey('gateway_token', $token);
    }

    /**
     */
    public function testDeletePaymentTokenIfUserIsNotAuthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The current customer isn\'t authorized.');

        $query = <<<QUERY
mutation {
  deletePaymentToken(
    public_hash: "ksdfk392ks"
  ) {
    result
  }
}
QUERY;
        $this->graphQlMutation($query, [], '');
    }

    /**
     * @magentoApiDataFixture Magento/Vault/_files/payment_tokens.php
     */
    public function testDeletePaymentTokenInvalidPublicHash()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Could not find a token using public hash: ksdfk392ks'
        );

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
mutation {
  deletePaymentToken(
    public_hash: "ksdfk392ks"
  ) {
    result
  }
}
QUERY;
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
