<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class UpdateCustomerAddressV2Test extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerInterface|null
     */
    private $customer;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customer = DataFixtureStorageManager::getStorage()->get('customer');
        $this->idEncoder = Bootstrap::getObjectManager()->get(Uid::class);
    }

    /**
     * @throws AuthenticationException
     */
    #[
        DataFixture(
            Customer::class,
            [
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'firstname' => 'John',
                        'lastname' => 'Doe',
                        'default_billing' => true,
                        'default_shipping' => true
                    ]
                ]
            ],
            'customer'
        )
    ]
    public function testUpdateCustomerAddressV2(): void
    {
        $address = current($this->customer->getAddresses());
        $addressUid = $this->idEncoder->encode((string) $address->getId());

        $this->assertEquals(
            [
                'updateCustomerAddressV2' => [
                    'uid' => $addressUid,
                    'city' => 'Cambridge',
                    'street' => ['123 Harvard Street'],
                    'postcode' => '02138',
                    'telephone' => '0987654321',
                    'firstname' => 'Jane',
                    'lastname' => 'Smith',
                    'country_id' => 'US',
                    'region' => [
                        'region_id' => 32
                    ]
                ]
            ],
            $this->graphQlMutation(
                $this->getUpdateCustomerAddressV2Mutation(),
                [
                    'uid' => $addressUid,
                    'input' => [
                        'city' => 'Cambridge',
                        'street' => ['123 Harvard Street'],
                        'postcode' => '02138',
                        'telephone' => '0987654321',
                        'firstname' => 'Jane',
                        'lastname' => 'Smith'
                    ]
                ],
                '',
                $this->getCustomerAuthHeaders($this->customer->getEmail())
            )
        );
    }

    /**
     * @throws AuthenticationException
     */
    #[
        DataFixture(
            Customer::class,
            [
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'firstname' => 'John',
                        'lastname' => 'Doe'
                    ]
                ]
            ],
            'customer'
        )
    ]
    public function testUpdateCustomerAddressV2WithInvalidUid(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find an address with the specified ID');

        $this->graphQlMutation(
            $this->getUpdateCustomerAddressV2Mutation(),
            [
                'uid' => $this->idEncoder->encode('999999'),
                'input' => [
                    'city' => 'Cambridge'
                ]
            ],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail())
        );
    }

    /**
     */
    #[
        DataFixture(
            Customer::class,
            [
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'firstname' => 'John',
                        'lastname' => 'Doe'
                    ]
                ]
            ],
            'customer'
        )
    ]
    public function testUpdateCustomerAddressV2WithoutAuthentication(): void
    {
        $address = current($this->customer->getAddresses());
        $addressUid = $this->idEncoder->encode((string) $address->getId());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized');

        $this->graphQlMutation(
            $this->getUpdateCustomerAddressV2Mutation(),
            [
                'uid' => $addressUid,
                'input' => [
                    'city' => 'Cambridge'
                ]
            ]
        );
    }

    /**
     * @throws AuthenticationException
     */
    #[
        DataFixture(
            Customer::class,
            [
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'firstname' => 'John',
                        'lastname' => 'Doe'
                    ]
                ]
            ],
            'customer1'
        ),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer2@example.com'
            ],
            'customer2'
        )
    ]
    public function testUpdateAnotherCustomerAddress(): void
    {
        $customer1 = DataFixtureStorageManager::getStorage()->get('customer1');
        $customer2 = DataFixtureStorageManager::getStorage()->get('customer2');

        $address = current($customer1->getAddresses());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Current customer does not have permission to get address with the specified ID'
        );

        $this->graphQlMutation(
            $this->getUpdateCustomerAddressV2Mutation(),
            [
                'uid' => $this->idEncoder->encode((string) $address->getId()),
                'input' => [
                    'city' => 'Cambridge'
                ]
            ],
            '',
            $this->getCustomerAuthHeaders($customer2->getEmail())
        );
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

    /**
     * Get updateCustomerAddressV2 mutation
     *
     * @return string
     */
    private function getUpdateCustomerAddressV2Mutation(): string
    {
        return <<<MUTATION
            mutation updateCustomerAddressV2(\$uid: ID!, \$input: CustomerAddressInput!) {
                updateCustomerAddressV2(uid: \$uid, input: \$input) {
                    uid
                    city
                    street
                    postcode
                    telephone
                    firstname
                    lastname
                    country_id
                    region {
                        region_id
                    }
                }
            }
        MUTATION;
    }
}
