<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GraphQlCache\CacheIdFactorProviders\Customer;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test class for IsLoggedIn CacheIdFactorProvider.
 */
class IsLoggedInProviderTest extends GraphQlAbstract
{
    /**
     * Tests cache is not generated for generateToken mutation and other post requests
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
        // Verify that the cache is not generated for generate token mutation
        $this->assertEquals('no-cache', $tokenResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $tokenResponse['headers']['Cache-Control']
        );
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
        //Verify that the cache is not generated for authorized mutation like createEmptyCart
        $this->assertEquals('no-cache', $createCustomerCartResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $createCustomerCartResponse['headers']['Cache-Control']
        );
        $cartId = $createCustomerCartResponse['body']['createEmptyCart'];

        $createGuestCartResponse = $this->graphQlMutationWithResponseHeaders($createEmptyCart);
        //Verify that cache is not generated for unauthorized post requests
        $this->assertEquals('no-cache', $createGuestCartResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $createGuestCartResponse['headers']['Cache-Control']
        );
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
        //Verify that cache is not generated for addSimpleProductsToCart mutation
        $this->assertEquals('no-cache', $addProductToCustomerCartResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $addProductToCustomerCartResponse['headers']['Cache-Control']
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
        // Get the guest headers
        $guestCartResponse = $this->graphQlMutationWithResponseHeaders('mutation{createEmptyCart}');
        $this->assertEquals('no-cache', $guestCartResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $guestCartResponse['headers']['Cache-Control']
        );

        // Get the customer token to send to the revoke mutation
        $generateToken = <<<MUTATION
mutation{
  generateCustomerToken(email:"customer@example.com", password:"password")
  {token}
}
MUTATION;
        $tokenResponse = $this->graphQlMutationWithResponseHeaders($generateToken);
        $this->assertArrayHasKey('generateCustomerToken', $tokenResponse['body']);
        $customerToken = $tokenResponse['body']['generateCustomerToken']['token'];
        $this->assertEquals('no-cache', $tokenResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $tokenResponse['headers']['Cache-Control']
        );

        // Revoke the token and check that cache is not generated
        $revokeCustomerToken = "mutation{revokeCustomerToken{result}}";
        $revokeResponse = $this->graphQlMutationWithResponseHeaders(
            $revokeCustomerToken,
            [],
            '',
            ['Authorization' => 'Bearer ' . $customerToken]
        );
        $this->assertEquals('no-cache', $revokeResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $revokeResponse['headers']['Cache-Control']
        );
    }
}
