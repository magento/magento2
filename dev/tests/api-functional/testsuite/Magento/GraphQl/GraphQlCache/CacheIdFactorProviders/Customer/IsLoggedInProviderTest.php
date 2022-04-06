<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GraphQlCache\CacheIdFactorProviders\Customer;

use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test class for IsLoggedIn CacheIdFactorProvider.
 */
class IsLoggedInProviderTest extends GraphQlAbstract
{
    /**
     * Tests that cache id header is generated for generateToken mutation and other post requests
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testCacheIdHeaderWithIsLoggedIn()
    {
        $email = 'customer@example.com';
        $password = 'password';
        $generateToken = <<<MUTATION
mutation{
  generateCustomerToken
  (
    email:"{$email}",
     password:"{$password}"
  )
  {
    token
  }
}
MUTATION;
        $tokenResponse = $this->graphQlMutationWithResponseHeaders($generateToken);
        // Verify that the the cache id is generated for generate token mutation
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $tokenResponse['headers']);
        $cacheIdCustomerToken = $tokenResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $cacheIdCustomerToken));
        $this->assertArrayHasKey('generateCustomerToken', $tokenResponse['body']);
        $customerToken = $tokenResponse['body']['generateCustomerToken']['token'];
        $createEmptyCart = <<<MUTATION
mutation{createEmptyCart}
MUTATION;

        $createCustomerCartResponse = $this->graphQlMutationWithResponseHeaders(
            $createEmptyCart,
            [],
            '',
            ['Authorization' => 'Bearer ' . $customerToken]
        );
        //Verify that the the cache id is generated for authorized mutation like createEmptyCart
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $createCustomerCartResponse['headers']);
        $cartId = $createCustomerCartResponse['body']['createEmptyCart'];
        $cacheIdCreateCustomerCart = $createCustomerCartResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $cacheIdCreateCustomerCart));
        $this->assertEquals($cacheIdCustomerToken, $cacheIdCreateCustomerCart);

        $createGuestCartResponse = $this->graphQlMutationWithResponseHeaders($createEmptyCart);
        //Verify that cache id is generated for unauthorized post requests
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $createGuestCartResponse['headers']);
        $cacheIdCreateGuestCart = $createGuestCartResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $cacheIdCreateGuestCart));
        //Verify that cache id generated for customer and guest are not equal
        $this->assertNotEquals($cacheIdCreateCustomerCart, $cacheIdCreateGuestCart);
        $addProductToCustomerCart = <<<MUTATION
mutation{
  addSimpleProductsToCart
  (input:{cart_id:"{$cartId}"
    cart_items:{
      data:{
        quantity:2
        sku:"simple_product"
      }    }  }  )
  {
    cart{ items{quantity product{sku}}}}}
MUTATION;
        $addProductToCustomerCartResponse = $this->graphQlMutationWithResponseHeaders(
            $addProductToCustomerCart,
            [],
            '',
            ['Authorization' => 'Bearer ' . $customerToken]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $addProductToCustomerCartResponse['headers']);
        //Verify that cache id generated for all subsequent operations by the customer remains consistent
        $this->assertEquals(
            $cacheIdCreateCustomerCart,
            $addProductToCustomerCartResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER]
        );
    }

    /**
     * Tests that cache id header resets to the one for guest when a customer token is revoked
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testCacheIdHeaderAfterRevokeToken()
    {
        // Get the guest cache id
        $guestCartResponse = $this->graphQlMutationWithResponseHeaders('mutation{createEmptyCart}');
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $guestCartResponse['headers']);
        $guestCacheId = $guestCartResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Get the customer cache id and token to send to the revoke mutation
        $generateToken = <<<MUTATION
mutation{
  generateCustomerToken(email:"customer@example.com", password:"password")
  {token}
}
MUTATION;
        $tokenResponse = $this->graphQlMutationWithResponseHeaders($generateToken);
        $this->assertArrayHasKey('generateCustomerToken', $tokenResponse['body']);
        $customerToken = $tokenResponse['body']['generateCustomerToken']['token'];
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $tokenResponse['headers']);
        $customerCacheId = $tokenResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($customerCacheId, $guestCacheId);

        // Revoke the token and check that it returns the guest cache id
        $revokeCustomerToken = "mutation{revokeCustomerToken{result}}";
        $revokeResponse = $this->graphQlMutationWithResponseHeaders(
            $revokeCustomerToken,
            [],
            '',
            ['Authorization' => 'Bearer ' . $customerToken]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $revokeResponse['headers']);
        $revokeCacheId = $revokeResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertEquals($guestCacheId, $revokeCacheId);
    }
}
