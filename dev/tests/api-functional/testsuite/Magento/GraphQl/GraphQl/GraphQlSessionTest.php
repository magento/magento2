<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GraphQl;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\Client;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;

/**
 * Test class to verify category uid, available as product aggregation type
 */
class GraphQlSessionTest extends GraphQlAbstract
{
    /**
     * @var Client
     */
    private $graphQlClient;

    /**
     * @var \Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheirtdoc
     */
    public function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->graphQlClient = $objectManager->get(Client::class);
    }

    /**
     * Test for checking if graphQL query sets session cookies
     *
     * Note: The reason why the first response doesn't have cookies, but the subsequent responses do is
     * because Magento/Framework/App/PageCache/Kernel.php removes Set-Cookie headers when the response has a
     * public Cache-Control.  This test asserts that behaviour.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @magentoConfigFixture graphql/session/disable 0
     */
    public function testCheckSessionCookieWithGetCategoryList(): void
    {
        $query = <<<QUERY
{
    categoryList{
        id
        uid
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        // Using cURL feature of flushing cookies upon completion of request
        $result = $this->graphQlClient->getWithResponseHeaders($query, [], '', [], true);
        $this->assertEmpty($result['cookies']);
        // perform secondary request after cookies have been flushed
        $result = $this->graphQlClient->getWithResponseHeaders($query, [], '', [], true);
        // may have other cookies than session
        $this->assertNotEmpty($result['cookies']);
        $this->assertAnyCookieMatchesRegex('/PHPSESSID=[a-z0-9]+;/', $result['cookies']);
        $this->assertCount(1, $result['body']['categoryList']);
    }

    /**
     * Test for checking if graphQL query does not set session cookies when session is disabled
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @magentoConfigFixture graphql/session/disable 1
     */
    public function testCheckSessionCookieNotPresentWithGetCategoryList(): void
    {
        $query = <<<QUERY
{
    categoryList{
        id
        uid
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        // CURL flushes cookies only upon completion of the request is the flag is set
        // perform graphql request with flushing cookies upon completion
        $result = $this->graphQlClient->getWithResponseHeaders($query, [], '', [], true);
        $this->assertEmpty($result['cookies']);

        // perform secondary request after cookies have been flushed
        $result = $this->graphQlClient->getWithResponseHeaders($query, [], '', []);

        // may have other cookies than session
        $this->assertNoCookiesMatchRegex('/PHPSESSID=[a-z0-9]+;/', $result['cookies']);
        $this->assertCount(1, $result['body']['categoryList']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoConfigFixture graphql/session/disable 0
     */
    public function testSessionStartsInAddProductToCartMutation()
    {
        $sku = 'simple_product';
        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);

        $result = $this->graphQlClient->postWithResponseHeaders($query, [], '', $this->getAuthHeaders(), true);
        // cookies are never empty and session is restarted for the authorized customer regardless current session
        $this->assertNotEmpty($result['cookies']);
        $this->assertAnyCookieMatchesRegex('/PHPSESSID=[a-z0-9]+;/', $result['cookies']);
        $result = $this->graphQlClient->postWithResponseHeaders($query, [], '', $this->getAuthHeaders());

        // cookies are never empty and session is restarted for the authorized customer
        // regardless current session and missing flush
        $this->assertNotEmpty($result['cookies']);
        $this->assertAnyCookieMatchesRegex('/PHPSESSID=[a-z0-9]+;/', $result['cookies']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoConfigFixture graphql/session/disable 1
     */
    public function testSessionDoesNotStartInAddProductToCartMutation()
    {
        $sku = 'simple_product';
        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId, $sku, $quantity);

        $result = $this->graphQlClient->postWithResponseHeaders($query, [], '', $this->getAuthHeaders(), true);
        // cookies may be empty or contain page cache private content version
        $this->assertNoCookiesMatchRegex('/PHPSESSID=[a-z0-9]+;/', $result['cookies']);
        $result = $this->graphQlClient->postWithResponseHeaders($query, [], '', $this->getAuthHeaders());
        // cookies may be empty or contain page cache private content version
        $this->assertNoCookiesMatchRegex('/PHPSESSID=[a-z0-9]+;/', $result['cookies']);
    }

    /**
     * Retrieve customer authorization headers
     *
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getAuthHeaders(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        return [sprintf('%s: %s', 'Authorization', 'Bearer ' . $customerToken)];
    }

    /**
     * @param string $maskedQuoteId
     * @param string $sku
     * @param float $quantity
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $sku, float $quantity): string
    {
        return <<<QUERY
mutation {
  addSimpleProductsToCart(input: {
    cart_id: "{$maskedQuoteId}",
    cart_items: [
      {
        data: {
          quantity: {$quantity}
          sku: "{$sku}"
        }
      }
    ]
  }) {
    cart {
      items {
        id
        quantity
        product {
          sku
        }
        prices {
          price {
           value
           currency
          }
          row_total {
           value
           currency
          }
          row_total_including_tax {
           value
           currency
          }
        }
      }
      shipping_addresses {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
        country {
          code
          label
        }
        __typename
      }
    }
  }
}
QUERY;
    }

    /**
     * Assert that at least one cookie in the array matches pattern.
     *
     * @param string $pattern
     * @param array $cookies
     * @return void
     */
    private function assertAnyCookieMatchesRegex(string $pattern, array $cookies): void
    {
        if (empty($cookies)) {
            return;
        }
        $result = false;
        foreach ($cookies as $cookie) {
            if (preg_match($pattern, $cookie)) {
                $result = true;
                break;
            }
        }
        $this->assertTrue($result, 'Failed asserting that any cookie in the array matches pattern: ' . $pattern);
    }

    /**
     * Assert that no cookie in the array matches pattern.
     *
     * @param string $pattern
     * @param array $cookies
     * @return void
     */
    private function assertNoCookiesMatchRegex(string $pattern, array $cookies): void
    {
        if (empty($cookies)) {
            return;
        }
        $result = true;
        foreach ($cookies as $cookie) {
            if (preg_match($pattern, $cookie)) {
                $result = false;
                break;
            }
        }
        $this->assertTrue($result, 'Failed assertion. At least one cookie in the array matches pattern: ' . $pattern);
    }

    /**
     * Tests that Magento\Customer\Model\Session works properly when graphql/session/disable=0
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture graphql/session/disable 0
     */
    public function testCustomerCanQueryOwnEmailUsingSession() : void
    {
        $query = '{customer{email}}';
        $result = $this->graphQlClient->postWithResponseHeaders($query, [], '', $this->getAuthHeaders(), true);
        // cookies are never empty and session is restarted for the authorized customer regardless current session
        $this->assertNotEmpty($result['cookies']);
        $this->assertAnyCookieMatchesRegex('/PHPSESSID=[a-z0-9]+;/', $result['cookies']);
        $this->assertEquals('customer@example.com', $result['body']['customer']['email'] ?? '');
        $result = $this->graphQlClient->postWithResponseHeaders($query, [], '', $this->getAuthHeaders());
        // cookies are never empty and session is restarted for the authorized customer
        // regardless current session and missing flush
        $this->assertNotEmpty($result['cookies']);
        $this->assertAnyCookieMatchesRegex('/PHPSESSID=[a-z0-9]+;/', $result['cookies']);
        $this->assertEquals('customer@example.com', $result['body']['customer']['email'] ?? '');
        /* Note: This third request is the actual one that tests that the session cookie is properly used.
         * This time we don't send the Authorization header and rely on Cookie header instead.
         * Because of bug in postWithResponseHeaders's $flushCookies parameter not being properly used,
         * We have to manually set cookie header ourselves. :-(
         */
        $cookiesToSend = '';
        foreach ($result['cookies'] as $cookie) {
            preg_match('/^([^;]*);/', $cookie, $matches);
            if (!strlen($matches[1] ?? '')) {
                continue;
            }
            if (!empty($cookiesToSend)) {
                $cookiesToSend .= '; ';
            }
            $cookiesToSend .= $matches[1];
        }
        $result = $this->graphQlClient->postWithResponseHeaders($query, [], '', ['Cookie: ' . $cookiesToSend]);
        $this->assertNotEmpty($result['cookies']);
        $this->assertAnyCookieMatchesRegex('/PHPSESSID=[a-z0-9]+;/', $result['cookies']);
        $this->assertEquals('customer@example.com', $result['body']['customer']['email'] ?? '');
    }
}
