<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
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

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var AddressRepositoryInterface
     */
    private $customerAddressRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->customerAddressRepository = $objectManager->get(AddressRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
          region: "AZ"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
         }
         same_as_shipping: true
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
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

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
     * Test case for deprecated `use_for_shipping` param.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewBillingAddressWithUseForShippingParameter()
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
          region: "AZ"
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
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

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
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressFromAddressBook()
    {
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
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertSavedBillingAddressFields($billingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testVerifyBillingAddressType()
    {
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
        __typename
      }
    }
  }
}
QUERY;

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $billingAddress = $response['setBillingAddressOnCart']['cart']['billing_address'];
        self::assertArrayHasKey('__typename', $billingAddress);
        self::assertEquals('BillingCartAddress', $billingAddress['__typename']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     */
    public function testSetNotExistedBillingAddressFromAddressBook()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a address with ID "100"');

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
          customer_address_id: 100
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewBillingAddressAndFromAddressBookAtSameTime()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        customer_address_id: 1
        address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AZ"
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

        self::expectExceptionMessage(
            'The billing address cannot contain "customer_address_id" and "address" at the same time.'
        );
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewBillingAddressWithoutCustomerAddressIdAndAddress()
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
            'The billing address must contain either "customer_address_id" or "address".'
        );
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_multishipping_with_two_shipping_addresses.php
     */
    public function testSetNewBillingAddressWithSameAsShippingAndMultishipping()
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
          region: "AZ"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
        }
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
            'Using the "same_as_shipping" option with multishipping is not possible.'
        );
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressToGuestCart()
    {
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
        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"{$maskedQuoteId}\""
        );

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testSetBillingAddressToAnotherCustomerCart()
    {
        $maskedQuoteId = $this->assignQuoteToCustomer('test_order_with_simple_product_without_address', 2);

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
        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"{$maskedQuoteId}\""
        );

        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer@search.example.com'));
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     *
     */
    public function testSetBillingAddressIfCustomerIsNotOwnerOfAddress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Current customer does not have permission to address with ID "1"');

        $maskedQuoteId = $this->assignQuoteToCustomer('test_order_with_simple_product_without_address', 2);

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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer2@search.example.com'));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @dataProvider dataProviderSetWithoutRequiredParameters
     * @param string $input
     * @param string $message
     * @throws \Exception
     */
    public function testSetBillingAddressWithoutRequiredParameters(string $input, string $message)
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $input = str_replace('cart_id_value', $maskedQuoteId, $input);

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      {$input}
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

        $this->expectExceptionMessage($message);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @return array
     */
    public function dataProviderSetWithoutRequiredParameters(): array
    {
        return [
            'missed_region' => [
                'cart_id: "cart_id_value"
                 billing_address: {
                    address: {
                        firstname: "test firstname"
                        lastname: "test lastname"
                        company: "test company"
                        street: ["test street 1", "test street 2"]
                        city: "test city"
                        postcode: "887766"
                        country_code: "US"
                        telephone: "88776655"
                        }
                    }',
                'Region is required'
            ],
            'missed_postal_code' => [
                'cart_id: "cart_id_value"
                 billing_address: {
                    address: {
                        firstname: "test firstname"
                        lastname: "test lastname"
                        company: "test company"
                        street: ["test street 1", "test street 2"]
                        city: "test city"
                        region: "TX"
                        country_code: "US"
                        telephone: "88776655"
                        }
                    }',
                '"postcode" is required. Enter and try again.'
            ],
            'wrong_required_region' => [
                'cart_id: "cart_id_value"
                 billing_address: {
                    address: {
                        firstname: "test firstname"
                        lastname: "test lastname"
                        company: "test company"
                        street: ["test street 1", "test street 2"]
                        region: "wrong region"
                        city: "test city"
                        country_code: "US"
                        postcode: "887766"
                        telephone: "88776655"
                        }
                    }',
                '"regionId" is required. Enter and try again'
            ],
            'wrong_required_region_name' => [
                'cart_id: "cart_id_value"
                 billing_address: {
                    address: {
                        firstname: "test firstname"
                        lastname: "test lastname"
                        company: "test company"
                        street: ["test street 1", "test street 2"]
                        region: "wrong region"
                        region_id: 45
                        city: "test city"
                        country_code: "US"
                        postcode: "887766"
                        telephone: "88776655"
                        }
                    }',
                'The region_id does not match the selected country or region'
            ],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewBillingAddressWithRedundantStreetLine()
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
          region: "AZ"
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
          region: "AZ"
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
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertNewAddressFields($billingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewBillingAddressWithSaveInAddressBook()
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
            region: "AZ"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
            save_in_address_book: true
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
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $customer = $this->customerRepository->get('customer@example.com');
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('parent_id', $customer->getId())->create();
        $addresses = $this->customerAddressRepository->getList($searchCriteria)->getItems();

        self::assertCount(0, $addresses);
        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);

        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertNewAddressFields($billingAddressResponse);

        foreach ($addresses as $address) {
            $this->customerAddressRepository->delete($address);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewBillingAddressWithNotSaveInAddressBook()
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
            region: "AZ"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
            save_in_address_book: false
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
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $customer = $this->customerRepository->get('customer@example.com');
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('parent_id', $customer->getId())->create();
        $addresses = $this->customerAddressRepository->getList($searchCriteria)->getItems();

        self::assertCount(0, $addresses);
        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);

        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertNewAddressFields($billingAddressResponse);

        foreach ($addresses as $address) {
            $this->customerAddressRepository->delete($address);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testWithInvalidBillingAddressInput()
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
          region: "AZ"
          postcode: "887766"
          country_code: "USS"
          telephone: "88776655"
          save_in_address_book: false
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
      }
    }
  }
}
QUERY;
        $this->expectExceptionMessage('Country is not available');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressesWithNotRequiredRegion()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
          address: {
            firstname: "Vasyl"
            lastname: "Doe"
            street: ["1 Svobody"]
            city: "Lviv"
            region: "Lviv"
            postcode: "00000"
            country_code: "UA"
            telephone: "555-555-55-55"
          }
        }
    }
  ) {
    cart {
      billing_address {
        region {
          label
        }
        country {
          code
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertEquals('UA', $cartResponse['billing_address']['country']['code']);
        self::assertEquals('Lviv', $cartResponse['billing_address']['region']['label']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressOnCartWithBothRegionAndRegionId()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
           firstname: "John"
            lastname: "Smith"
            company: "Magento"
            street: ["Magento Pkwy", "Main Street"]
            city: "APO"
            region: "AE"
            region_id: 10
            postcode: "09369"
            country_code: "US"
            telephone: "8675309"
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
        region {
          code
          label
          region_id
        }
        __typename
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        $this->assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $expectedRegionCode = "AE";
        $expectedRegionLabel = "Armed Forces Middle East";
        $this->assertEquals($expectedRegionCode, $billingAddressResponse['region']['code']);
        $this->assertEquals($expectedRegionLabel, $billingAddressResponse['region']['label']);
        $this->assertEquals(10, $billingAddressResponse['region']['region_id']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressOnCartWithFreeFormRegionForNoRequiredCountry()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
           firstname: "John"
            lastname: "Smith"
            company: "Magento"
            street: ["test street 1", "test street 2"]
            city: "London"
            region: "Some"
            postcode: "887766"
            country_code: "GB"
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
        region {
          code
          label
          region_id
        }
        __typename
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        $this->assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $expectedRegionCode = "Some";
        $expectedRegionLabel = "Some";
        $this->assertEquals($expectedRegionCode, $billingAddressResponse['region']['code']);
        $this->assertEquals($expectedRegionLabel, $billingAddressResponse['region']['label']);
        $this->assertEquals(null, $billingAddressResponse['region']['region_id']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressOnCartWithRegionOnExistingDuplicateRegionCode()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
           firstname: "John"
            lastname: "Smith"
            company: "Magento"
            street: ["Magento Pkwy", "Main Street"]
            city: "APO"
            region: "AE"
            postcode: "09369"
            country_code: "US"
            telephone: "8675309"
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
        region {
          code
          label
        }
        __typename
      }
    }
  }
}
QUERY;
        $this->expectExceptionMessage(
            'Region input is ambiguous. Specify region_id.'
        );
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressOnCartWithRegionOnExistingDuplicateRegionCodeWithWrongRegionId()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
           firstname: "John"
            lastname: "Smith"
            company: "Magento"
            street: ["Magento Pkwy", "Main Street"]
            city: "APO"
            region: "AE"
            region_id: 17
            postcode: "09369"
            country_code: "US"
            telephone: "8675309"
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
        region {
          code
          label
        }
        __typename
      }
    }
  }
}
QUERY;
        $this->expectExceptionMessage(
            'The region_id does not match the selected country or region'
        );
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressOnCartWithWrongRegionId()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
           firstname: "John"
            lastname: "Smith"
            company: "Magento"
            street: ["Magento Pkwy", "Main Street"]
            city: "Dallas"
            region: "TX"
            region_id: 10
            postcode: "09369"
            country_code: "US"
            telephone: "8675309"
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
        region {
          code
          label
        }
        __typename
      }
    }
  }
}
QUERY;
        $this->expectExceptionMessage(
            'The region_id does not match the selected country or region'
        );
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressOnCartWithCorrectRegionId()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
           firstname: "John"
            lastname: "Smith"
            company: "Magento"
            street: ["Magento Pkwy", "Main Street"]
            city: "Dallas"
            region: "TX"
            region_id: 57
            postcode: "09369"
            country_code: "US"
            telephone: "8675309"
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
        region {
          code
          label
        }
        __typename
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        $this->assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $expectedRegionCode = "TX";
        $expectedRegionLabel = "Texas";
        $this->assertEquals($expectedRegionCode, $billingAddressResponse['region']['code']);
        $this->assertEquals($expectedRegionLabel, $billingAddressResponse['region']['label']);
    }

    /**
     * Verify the all the whitelisted fields for a New Address Object
     *
     * @param array $addressResponse
     * @param string $addressType
     */
    private function assertNewAddressFields(array $addressResponse, string $addressType = 'BillingCartAddress'): void
    {
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
     * Verify the all the whitelisted fields for a Address Object
     *
     * @param array $billingAddressResponse
     */
    private function assertSavedBillingAddressFields(array $billingAddressResponse): void
    {
        $assertionMap = [
            ['response_field' => 'firstname', 'expected_value' => 'John'],
            ['response_field' => 'lastname', 'expected_value' => 'Smith'],
            ['response_field' => 'company', 'expected_value' => 'CompanyName'],
            ['response_field' => 'street', 'expected_value' => [0 => 'Green str, 67']],
            ['response_field' => 'city', 'expected_value' => 'CityM'],
            ['response_field' => 'postcode', 'expected_value' => '75477'],
            ['response_field' => 'telephone', 'expected_value' => '3468676'],
            ['response_field' => 'country', 'expected_value' => ['code' => 'US', 'label' => 'US']],
        ];

        $this->assertResponseFields($billingAddressResponse, $assertionMap);
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }

    /**
     * @param string $reservedOrderId
     * @param int $customerId
     * @return string
     */
    private function assignQuoteToCustomer(
        string $reservedOrderId = 'test_order_with_simple_product_without_address',
        int $customerId = 1
    ): string {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $quote->setCustomerId($customerId);
        $this->quoteResource->save($quote);
        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $maskedQuoteId
     * @param string $shippingMethodCode
     * @param string $shippingCarrierCode
     * @return string
     */
    private function getSetShippingMethodsQuery(
        string $maskedQuoteId,
        string $shippingMethodCode,
        string $shippingCarrierCode
    ): string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input:
    {
      cart_id: "$maskedQuoteId",
      shipping_methods: [{
        carrier_code: "$shippingCarrierCode"
        method_code: "$shippingMethodCode"
      }]
    }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
          carrier_title
          method_title
          amount {
            value
            currency
          }
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressAndPlaceOrder()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        same_as_shipping: true
         address: {
          firstname: "test firstname"
            lastname: "test lastname"
            company: "test company"
            street: ["test street 1", "test street 2"]
            city: "test city"
            region: "AZ"
            postcode: "88776"
            country_code: "US"
            telephone: "88776655"
            save_in_address_book: true
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
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->graphQlMutation(
            $this->getSetShippingMethodsQuery($maskedQuoteId, 'flatrate', 'flatrate'),
            [],
            '',
            $this->getHeaderMap()
        );
        $this->graphQlMutation(
            $this->getSetPaymentMethodQuery(
                $maskedQuoteId,
                'checkmo'
            ),
            [],
            '',
            $this->getHeaderMap()
        );
        $this->graphQlMutation(
            $this->getPlaceOrderQuery($maskedQuoteId),
            [],
            '',
            $this->getHeaderMap()
        );
        $customer = $this->customerRepository->get('customer@example.com');
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('parent_id', $customer->getId())->create();
        $addresses = $this->customerAddressRepository->getList($searchCriteria)->getItems();

        self::assertCount(1, $addresses);
        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        foreach ($addresses as $address) {
            $this->customerAddressRepository->delete($address);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetBillingAddressWithDefaultValueOfSaveInAddressBookAndPlaceOrder()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
        same_as_shipping: true
         address: {
          firstname: "test firstname"
            lastname: "test lastname"
            company: "test company"
            street: ["test street 1", "test street 2"]
            city: "test city"
            region: "AZ"
            postcode: "88776"
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
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->graphQlMutation(
            $this->getSetShippingMethodsQuery($maskedQuoteId, 'flatrate', 'flatrate'),
            [],
            '',
            $this->getHeaderMap()
        );
        $this->graphQlMutation(
            $this->getSetPaymentMethodQuery(
                $maskedQuoteId,
                'checkmo'
            ),
            [],
            '',
            $this->getHeaderMap()
        );
        $this->graphQlMutation(
            $this->getPlaceOrderQuery($maskedQuoteId),
            [],
            '',
            $this->getHeaderMap()
        );
        $customer = $this->customerRepository->get('customer@example.com');
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('parent_id', $customer->getId())->create();
        $addresses = $this->customerAddressRepository->getList($searchCriteria)->getItems();

        $this->assertCount(1, $addresses);
        $this->assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        foreach ($addresses as $address) {
            $this->customerAddressRepository->delete($address);
        }
    }

    /**
     * @param string $maskedQuoteId
     * @param string $methodCode
     * @return string
     */
    private function getSetPaymentMethodQuery(
        string $maskedQuoteId,
        string $methodCode
    ) : string {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$maskedQuoteId"
      payment_method: {
          code: "$methodCode"
      }
  }) {
    cart {
      selected_payment_method {
        code
      }
    }
  }
}
QUERY;
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getPlaceOrderQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
    order {
      order_number
    }
  }
}
QUERY;
    }
}
