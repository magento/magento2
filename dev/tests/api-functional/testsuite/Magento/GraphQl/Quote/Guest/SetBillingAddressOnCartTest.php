<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteIdMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for set billing address on cart mutation
 */
class SetBillingAddressOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * Tests setting the billing address on the cart by providing address input information.
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewBillingAddress()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AL"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
         }
      }
    }
  ) {
    cart {
      billing_address {
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
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertNewAddressFields($billingAddressResponse);
        $this->assertNewAddressFields($billingAddressResponse);
    }

    /**
     * Tests that the "use_for_shipping" option sets the provided billing address for shipping as well.
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewBillingAddressWithUseForShipping()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AL"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
         }
         use_for_shipping: true
      }
    }
  ) {
    cart {
      billing_address {
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
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        self::assertArrayHasKey('shipping_addresses', $cartResponse);
        $shippingAddressResponse = current($cartResponse['shipping_addresses']);
        $this->assertNewAddressFields($billingAddressResponse);
        $this->assertNewAddressFields($shippingAddressResponse, 'ShippingCartAddress');
    }

    /**
     * Tests that a guest cannot set the billing address on a logged-in customer's cart.
     *
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     */
    public function testSetBillingAddressToCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AL"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
        }
      }
    }
  ) {
    cart {
      billing_address {
        city
      }
    }
  }
}
QUERY;

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlMutation($query);
    }

    /**
     * Tests that a guest cannot use a logged-in customer's saved address as the billing address on their own cart.
     *
     * _security
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     */
    public function testSetBillingAddressFromAddressBook()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
          customer_address_id: 1
        }
    }
  ) {
    cart {
      billing_address {
        city
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * Tests that a guest cannot set the billing address on a cart that does not exist.
     */
    public function testSetBillingAddressOnNonExistentCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a cart with ID "non_existent_masked_id"');

        $maskedQuoteId = 'non_existent_masked_id';
        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AL"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
        }
      }
    }
  ) {
    cart {
      billing_address {
        city
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * Tests that an error is thrown when the street information exceeds the maximum number of allowed lines.
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressRedundantStreetLine()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2", "test street 3"]
          city: "test city"
          region: "AL"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
        }
      }
    }
  ) {
    cart {
      billing_address {
        firstname
      }
    }
  }
}
QUERY;

        self::expectExceptionMessage('"Street Address" cannot contain more than 2 lines.');
        $this->graphQlMutation($query);
    }

    /**
     * Tests that setting a new billing address succeeds with a lower case "country_code".
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressWithLowerCaseCountry()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AL"
          postcode: "887766"
          country_code: "us"
          telephone: "88776655"
        }
      }
    }
  ) {
    cart {
      billing_address {
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
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertNewAddressFields($billingAddressResponse);
    }

    /**
     * Tests that an error is thrown when a billing address is not provided.
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressWithoutProvidingAddress()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        use_for_shipping: true
      }
    }
  ) {
    cart {
      billing_address {
        city
      }
    }
  }
}
QUERY;

        self::expectExceptionMessage(
            'The billing address must contain either "customer_address_id", "address", or "same_as_shipping".'
        );
        $this->graphQlMutation($query);
    }

    /**
     * Tests that the "same_as_shipping" option uses the cart's existing shipping address for the billing address.
     *
     * Ignores any address input as well as the "use_for_shipping" option when "same_as_shipping" is true.
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testSetBillingAddressWithSameAsShipping()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        same_as_shipping: true
        use_for_shipping: true
        address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AL"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
        }
      }
    }
  ) {
    cart {
      billing_address {
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
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];

        // Assert billing address has been set according to the cart's shipping address
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertNewAddressFieldsFromShippingAddress($billingAddressResponse);

        // Assert the shipping address is unchanged
        self::assertArrayHasKey('shipping_addresses', $cartResponse);
        $shippingAddressResponse = current($cartResponse['shipping_addresses']);
        $this->assertNewAddressFieldsFromShippingAddress($shippingAddressResponse, 'ShippingCartAddress');
    }

    /**
     * Tests that the "same_as_shipping" option cannot be used when a shipping address has not been set on the cart.
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressWithSameAsShippingWithoutShippingAddressOnCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        same_as_shipping: true
      }
    }
  ) {
    cart {
      billing_address {
        city
      }
    }
  }
}
QUERY;
        self::expectExceptionMessage(
            'Could not use the "same_as_shipping" option, because the shipping address has not been set.'
        );
        $this->graphQlMutation($query);
    }

    /**
     * Tests that the "same_as_shipping" option cannot be used when multi-shipping is applied.
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_multishipping_with_two_shipping_addresses.php
     */
    public function testSetBillingAddressWithSameAsShippingAndMultishipping()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        same_as_shipping: true
      }
    }
  ) {
    cart {
      billing_address {
        city
      }
    }
  }
}
QUERY;

        self::expectExceptionMessage(
            'Could not use the "same_as_shipping" option, because multiple shipping addresses have been set.'
        );
        $this->graphQlMutation($query);
    }

    /**
     * Test graphql mutation setting middlename, prefix, suffix and fax in billing address
     *
     * @throws LocalizedException
     */
    #[
        DataFixture(GuestCart::class, as: 'quote'),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$quote.id$'], as: 'mask'),
    ]
    public function testSetMiddlenamePrefixSuffixFaxBillingAddress()
    {
        /** @var QuoteIdMask $quoteMask */
        $quoteMask = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage()->get('mask');

        $expectedResult = [
            'setBillingAddressOnCart' => [
                'cart' => [
                    'billing_address' => [
                        'firstname' => 'test firstname',
                        'lastname' => 'test lastname',
                        'middlename' => 'test middlename',
                        'prefix' => 'Mr.',
                        'suffix' => 'Jr.',
                        'fax' => '5552224455',
                        'company' => 'test company',
                        'street' => [
                            'test street 1',
                            'test street 2',
                        ],
                        'city' => 'test city',
                        'postcode' => '887766',
                        'telephone' => '88776655',
                        'country' => [
                            'code' => 'US',
                            'label' => 'US',
                        ],
                        '__typename' => 'BillingCartAddress'
                    ]
                ]
            ]
        ];

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "{$quoteMask->getMaskedId()}"
      billing_address: {
         address: {
          firstname: "test firstname"
          lastname: "test lastname"
          middlename: "test middlename"
          prefix: "Mr."
          suffix: "Jr."
          fax: "5552224455"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AL"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
         }
      }
    }
  ) {
    cart {
      billing_address {
        firstname
        lastname
        middlename
        prefix
        suffix
        fax
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
        $response = $this->graphQlMutation($query);
        $this->assertEquals($expectedResult, $response);
    }

    /**
     * Verify all the whitelisted fields for a New Address Object
     *
     * @param array $addressResponse
     * @param string $addressType
     */
    private function assertNewAddressFields(
        array $addressResponse,
        string $addressType = 'BillingCartAddress'
    ): void {
        $assertionMap = [
            ['response_field' => 'firstname', 'expected_value' => 'test firstname'],
            ['response_field' => 'lastname', 'expected_value' => 'test lastname'],
            ['response_field' => 'company', 'expected_value' => 'test company'],
            ['response_field' => 'street', 'expected_value' => [0 => 'test street 1', 1 => 'test street 2']],
            ['response_field' => 'city', 'expected_value' => 'test city'],
            ['response_field' => 'postcode', 'expected_value' => '887766'],
            ['response_field' => 'telephone', 'expected_value' => '88776655'],
            ['response_field' => 'country', 'expected_value' => ['code' => 'US', 'label' => 'US']],
            ['response_field' => '__typename', 'expected_value' => $addressType]
        ];

        $this->assertResponseFields($addressResponse, $assertionMap);
    }

    /**
     * Verify that the fields for the specified quote address match the shipping address from the fixture.
     *
     * Useful for verifying scenarios with the "same_as_shipping" option.
     *
     * @param array $addressResponse
     * @param string $addressType
     */
    private function assertNewAddressFieldsFromShippingAddress(
        array $addressResponse,
        string $addressType = 'BillingCartAddress'
    ): void {
        $assertionMap = [
            ['response_field' => 'firstname', 'expected_value' => 'John'],
            ['response_field' => 'lastname', 'expected_value' => 'Smith'],
            ['response_field' => 'company', 'expected_value' => 'CompanyName'],
            ['response_field' => 'street', 'expected_value' => [0 => 'Green str, 67']],
            ['response_field' => 'city', 'expected_value' => 'CityM'],
            ['response_field' => 'postcode', 'expected_value' => '75477'],
            ['response_field' => 'telephone', 'expected_value' => 3468676],
            ['response_field' => 'country', 'expected_value' => ['code' => 'US', 'label' => 'US']],
            ['response_field' => '__typename', 'expected_value' => $addressType]
        ];

        $this->assertResponseFields($addressResponse, $assertionMap);
    }
}
