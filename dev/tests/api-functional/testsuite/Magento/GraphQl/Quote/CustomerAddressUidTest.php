<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CustomerAddressUidTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(
            ProductFixture::class,
            ['price' => 10.00],
            'product'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testSetShippingAddressWithCustomerAddressUid(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        // Create address programmatically
        $address = $this->createCustomerAddress($customer);
        $addressId = base64_encode((string)$address->getId());

        $this->assertEquals(
            [
                'setShippingAddressesOnCart' => [
                    'cart' => [
                        'shipping_addresses' => [
                            [
                                'firstname' => $address->getFirstname(),
                                'lastname' => $address->getLastname(),
                                'company' => $address->getCompany(),
                                'street' => $address->getStreet(),
                                'city' => $address->getCity(),
                                'postcode' => $address->getPostcode(),
                                'telephone' => $address->getTelephone(),
                                'country' => [
                                    'code' => 'US'
                                ],
                                'customer_address_uid' => $addressId
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlMutation(
                $this->getSetShippingAddressWithCustomerAddressUidMutation($maskedQuoteId, $addressId),
                [],
                '',
                $this->getCustomerAuthHeaders($customer->getEmail())
            )
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(
            ProductFixture::class,
            ['price' => 20.00],
            'product'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testSetBillingAddressWithCustomerAddressUid(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        // Create address programmatically
        $address = $this->createCustomerAddress($customer);
        $addressId = base64_encode((string)$address->getId());

        $this->assertEquals(
            [
                'setBillingAddressOnCart' => [
                    'cart' => [
                        'billing_address' => [
                            'firstname' => $address->getFirstname(),
                            'lastname' => $address->getLastname(),
                            'company' => $address->getCompany(),
                            'street' => $address->getStreet(),
                            'city' => $address->getCity(),
                            'postcode' => $address->getPostcode(),
                            'telephone' => $address->getTelephone(),
                            'country' => [
                                'code' => 'US'
                            ],
                            'customer_address_uid' => $addressId
                        ]
                    ]
                ]
            ],
            $this->graphQlMutation(
                $this->getSetBillingAddressWithCustomerAddressUidMutation($maskedQuoteId, $addressId),
                [],
                '',
                $this->getCustomerAuthHeaders($customer->getEmail())
            )
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(
            ProductFixture::class,
            ['price' => 10.00],
            'product'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testCartAddressInterfaceFieldsWithCustomerAddress(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $address = $this->createCustomerAddress($customer);
        $addressId = base64_encode((string)$address->getId());

        // Set shipping address using customer address
        $this->graphQlMutation(
            $this->getSetShippingAddressWithCustomerAddressUidMutation($maskedQuoteId, $addressId),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail())
        );

        // Set billing address using customer address
        $this->graphQlMutation(
            $this->getSetBillingAddressWithCustomerAddressUidMutation($maskedQuoteId, $addressId),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail())
        );

        $this->assertEquals(
            [
                'cart' => [
                    'shipping_addresses' => [
                        [
                            'id' => (int) $address->getId(),
                            'customer_address_uid' => $addressId,
                            'firstname' => $address->getFirstname(),
                            'lastname' => $address->getLastname(),
                            'street' => $address->getStreet(),
                            'city' => $address->getCity(),
                            'postcode' => $address->getPostcode(),
                            'telephone' => $address->getTelephone(),
                            'country' => [
                                'code' => 'US'
                            ]
                        ]
                    ],
                    'billing_address' => [
                        'id' => (int) $address->getId(),
                        'customer_address_uid' => $addressId,
                        'firstname' => $address->getFirstname(),
                        'lastname' => $address->getLastname(),
                        'street' => $address->getStreet(),
                        'city' => $address->getCity(),
                        'postcode' => $address->getPostcode(),
                        'telephone' => $address->getTelephone(),
                        'country' => [
                            'code' => 'US'
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartWithAddressesQuery($maskedQuoteId),
                [],
                '',
                $this->getCustomerAuthHeaders($customer->getEmail())
            )
        );
    }

    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 20.00],
            'product'
        ),
        DataFixture(GuestCart::class, ['currency' => 'USD'], 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(AddProductToCart::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ])
    ]
    public function testCartAddressInterfaceFieldsWithGuestAddress(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();

        // First set addresses on guest cart
        $this->graphQlMutation(
            $this->getSetGuestAddressesMutation($maskedQuoteId)
        );

        $this->assertEquals(
            [
                'cart' => [
                    'shipping_addresses' => [
                        [
                            'id' => null,
                            'customer_address_uid' => null,
                            'firstname' => 'Jane',
                            'lastname' => 'Guest',
                            'street' => ['123 Guest St'],
                            'city' => 'Guest City',
                            'postcode' => '12345',
                            'telephone' => '555-0123',
                            'country' => [
                                'code' => 'US'
                            ]
                        ]
                    ],
                    'billing_address' => [
                        'id' => null,
                        'customer_address_uid' => null,
                        'firstname' => 'Jane',
                        'lastname' => 'Guest',
                        'street' => ['123 Guest St'],
                        'city' => 'Guest City',
                        'postcode' => '12345',
                        'telephone' => '555-0123',
                        'country' => [
                            'code' => 'US'
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery($this->getCartWithAddressesQuery($maskedQuoteId))
        );
    }

    /**
     * Create a customer address programmatically
     */
    private function createCustomerAddress($customer): AddressInterface
    {
        $addressRepository = Bootstrap::getObjectManager()->get(AddressRepositoryInterface::class);
        $addressFactory = Bootstrap::getObjectManager()->get(AddressInterfaceFactory::class);

        $address = $addressFactory->create();
        $address->setCustomerId($customer->getId())
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setCompany('Test Company')
            ->setStreet(['123 Test Street'])
            ->setCity('Boston')
            ->setPostcode('02108')
            ->setCountryId('US')
            ->setRegionId(32)
            ->setTelephone('1234567890')
            ->setIsDefaultBilling(true)
            ->setIsDefaultShipping(true);

        return $addressRepository->save($address);
    }

    /**
     * Get mutation for setting shipping address using customer address uid
     *
     * @param string $cartId
     * @param string $uid
     * @return string
     */
    private function getSetShippingAddressWithCustomerAddressUidMutation(string $cartId, string $uid): string
    {
        return <<<MUTATION
            mutation {
                setShippingAddressesOnCart(input: {
                    cart_id: "{$cartId}",
                    shipping_addresses: [{
                        customer_address_uid: "{$uid}"
                    }]
                }) {
                    cart {
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
                            }
                            customer_address_uid
                        }
                    }
                }
            }
        MUTATION;
    }

    /**
     * Get mutation for setting billing address using customer address uid
     *
     * @param string $cartId
     * @param string $uid
     * @return string
     */
    private function getSetBillingAddressWithCustomerAddressUidMutation(string $cartId, string $uid): string
    {
        return <<<MUTATION
            mutation {
                setBillingAddressOnCart(input: {
                    cart_id: "{$cartId}",
                    billing_address: {
                        customer_address_uid: "{$uid}"
                    }
                }) {
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
                            }
                            customer_address_uid
                        }
                    }
                }
            }
        MUTATION;
    }

    /**
     * Get query for fetching cart with shipping and billing addresses
     *
     * @param string $cartId
     * @return string
     */
    private function getCartWithAddressesQuery(string $cartId): string
    {
        return <<<QUERY
            query {
                cart(cart_id: "{$cartId}") {
                    shipping_addresses {
                        id
                        customer_address_uid
                        firstname
                        lastname
                        street
                        city
                        postcode
                        telephone
                        country {
                            code
                        }
                    }
                    billing_address {
                        id
                        customer_address_uid
                        firstname
                        lastname
                        street
                        city
                        postcode
                        telephone
                        country {
                            code
                        }
                    }
                }
            }
        QUERY;
    }

    /**
     * Get mutation for setting shipping and billing addresses for guest cart
     *
     * @param string $cartId
     * @return string
     */
    private function getSetGuestAddressesMutation(string $cartId): string
    {
        return <<<MUTATION
            mutation {
                setShippingAddressesOnCart(input: {
                    cart_id: "{$cartId}",
                    shipping_addresses: [{
                        address: {
                            firstname: "Jane",
                            lastname: "Guest",
                            company: "Test Company",
                            street: ["123 Guest St"],
                            city: "Guest City",
                            region: "CA",
                            postcode: "12345",
                            country_code: "US",
                            telephone: "555-0123"
                        }
                    }]
                }) {
                    cart {
                        shipping_addresses {
                            uid
                        }
                    }
                }
                setBillingAddressOnCart(input: {
                    cart_id: "{$cartId}",
                    billing_address: {
                        address: {
                            firstname: "Jane",
                            lastname: "Guest",
                            company: "Test Company",
                            street: ["123 Guest St"],
                            city: "Guest City",
                            region: "CA",
                            postcode: "12345",
                            country_code: "US",
                            telephone: "555-0123"
                        }
                    }
                }) {
                    cart {
                        billing_address {
                            uid
                        }
                    }
                }
            }
        MUTATION;
    }

    /**
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->customerTokenService->createCustomerAccessToken($email, 'password')
        ];
    }
}
