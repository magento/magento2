<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\EavGraphQl\Model\Uid;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Bootstrap as TestBootstrap;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * GraphQl tests for @see \Magento\CustomerGraphQl\Model\Customer\GetCustomer.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetCustomerTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /** @var Uid $uid  */
    private $uid;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->customerAuthUpdate = $this->objectManager->get(CustomerAuthUpdate::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->uid = $this->objectManager->get(Uid::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomer()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customer {
        id
        firstname
        lastname
        email
    }
}
QUERY;
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        $this->assertNull($response['customer']['id']);
        $this->assertEquals('John', $response['customer']['firstname']);
        $this->assertEquals('Smith', $response['customer']['lastname']);
        $this->assertEquals($currentEmail, $response['customer']['email']);
    }

    /**
     */
    public function testGetCustomerIfUserIsNotAuthorized()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/User/_files/user_with_role.php
     * @return void
     */
    public function testGetCustomerIfUserHasWrongType(): void
    {
        /** @var $adminTokenService AdminTokenServiceInterface */
        $adminTokenService = $this->objectManager->get(AdminTokenServiceInterface::class);
        $adminToken = $adminTokenService->createAdminAccessToken('adminUser', TestBootstrap::ADMIN_PASSWORD);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $this->graphQlQuery(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $adminToken]
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerIfAccountIsLocked()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $customer = $this->customerRepository->get($currentEmail);

        $this->lockCustomer((int)$customer->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The account is locked.');

        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
    }

    /**
     * @magentoConfigFixture customer/create_account/confirm 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testAccountIsNotConfirmed()
    {
        $this->expectExceptionMessage("This account isn't confirmed. Verify and try again.");
        $customerEmail = 'customer@example.com';
        $currentPassword = 'password';
        $customer = $this->customerRepository->get($customerEmail);
        $headersMap = $this->getCustomerAuthHeaders($customerEmail, $currentPassword);
        $customer = $this->customerRepository->getById((int)$customer->getId())
            ->setConfirmation(AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED);
        $this->customerRepository->save($customer);
        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $this->graphQlQuery($query, [], '', $headersMap);
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

    /**
     * @param int $customerId
     * @return void
     */
    private function lockCustomer(int $customerId): void
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
        $customerSecure->setLockExpires('2030-12-31 00:00:00');
        $this->customerAuthUpdate->saveAuth($customerId);
    }

    #[
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => 'shoe_size',
                'sort_order' => 1,
                'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_group_id' => 1,
            ],
            'attribute_1'
        ),
        DataFixture(
            Customer::class,
            [
                'email' => 'john@doe.com',
                'custom_attributes' => [
                    'shoe_size' => '42'
                ]
            ],
            'customer'
        ),
    ]
    public function testGetCustomAttributes()
    {
        $currentEmail = 'john@doe.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
        custom_attributes {
            uid
            code
            ... on AttributeValue {
                value
            }
            ... on AttributeSelectedOptions {
                selected_options {
                    uid
                    label
                    value
                }
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        /** @var AttributeInterface $attribute1 */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute_1');
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $this->assertEquals(
            [
                'customer' => [
                    'firstname' => $customer->getFirstname(),
                    'lastname' => $customer->getLastname(),
                    'email' => $customer->getEmail(),
                    'custom_attributes' => [
                        [
                            'uid' => $this->uid->encode(
                                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                                $attribute1->getAttributeCode()
                            ),
                            'code' => $attribute1->getAttributeCode(),
                            'value' => '42'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    #[
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => 'planet',
                'sort_order' => 1,
                'attribute_set_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_group_id' => 1,
            ],
            'attribute_1'
        ),
        DataFixture(
            Customer::class,
            [
                'email' => 'john@doe.com',
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'default_billing' => true,
                        'default_shipping' => true,
                        'custom_attributes' => [
                           'planet' => 'Earth'
                        ],
                    ],
                ],
            ],
            'customer'
        ),
    ]
    public function testGetAddressCustomAttributes()
    {
        $currentEmail = 'john@doe.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
        addresses {
            country_id
            custom_attributesV2 {
                uid
                code
                ... on AttributeValue {
                    value
                }
                ... on AttributeSelectedOptions {
                    selected_options {
                        uid
                        label
                        value
                    }
                }
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        /** @var AttributeInterface $attribute1 */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute_1');
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $this->assertEquals(
            [
                'customer' => [
                    'firstname' => $customer->getFirstname(),
                    'lastname' => $customer->getLastname(),
                    'email' => $customer->getEmail(),
                    'addresses' => [
                        [
                            'country_id' => 'US',
                            'custom_attributesV2' => [
                                [
                                    'uid' => $this->uid->encode(
                                        AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                                        $attribute1->getAttributeCode()
                                    ),
                                    'code' => $attribute1->getAttributeCode(),
                                    'value' => 'Earth'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    #[
        DataFixture(
            Customer::class,
            [
                'email' => 'john@doe.com',
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'default_billing' => true,
                        'default_shipping' => true,
                    ],
                ],
            ],
            'customer'
        ),
    ]
    public function testGetNoAddressCustomAttributes()
    {
        $currentEmail = 'john@doe.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customer {
        id
        firstname
        lastname
        email
        addresses {
            country_id
            custom_attributesV2 {
                uid
                code
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $this->assertEquals(
            [
                'customer' => [
                    'id' => null,
                    'firstname' => $customer->getFirstname(),
                    'lastname' => $customer->getLastname(),
                    'email' => $customer->getEmail(),
                    'addresses' => [
                        [
                            'country_id' => 'US',
                            'custom_attributesV2' => []
                        ]
                    ]
                ]
            ],
            $response
        );
    }
}
