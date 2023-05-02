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
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Test\Fixture\AttributeOption as AttributeOptionFixture;
use Magento\EavGraphQl\Model\Uid as EAVUid;
use Magento\Framework\GraphQl\Query\Uid;
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
    private const CUSTOM_ATTRIBUTES_QUERY = <<<QUERY
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

    /** @var EAVUid $eavUid  */
    private $eavUid;

    /**
     * @var Uid $uid
     */
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
        $this->eavUid = $this->objectManager->get(EAVUid::class);
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
                'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_group_id' => 1,
            ],
            'varchar_custom_customer_attribute'
        ),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'multiselect',
                'source_model' => Table::class,
                'backend_model' => ArrayBackend::class,
                'attribute_code' => 'shoe_color',
                'attribute_group_id' => 1,
            ],
            'multiselect_custom_customer_attribute'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$multiselect_custom_customer_attribute.attribute_code$',
                'label' => 'red',
                'sort_order' => 10
            ],
            'multiselect_custom_customer_attribute_option_1'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$multiselect_custom_customer_attribute.attribute_code$',
                'sort_order' => 20,
                'label' => 'white',
                'is_default' => true
            ],
            'multiselect_custom_customer_attribute_option_2'
        ),
        DataFixture(
            Customer::class,
            [
                'email' => 'john@doe.com',
                'custom_attributes' => [
                    [
                        'attribute_code' => 'shoe_size',
                        'value' => '42'
                    ],
                    [
                        'attribute_code' => 'shoe_color',
                        'selected_options' => [
                            ['value' => '$multiselect_custom_customer_attribute_option_1.value$'],
                            ['value' => '$multiselect_custom_customer_attribute_option_2.value$']
                        ],
                    ],
                ],
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
    public function testGetCustomAttributes()
    {
        $currentEmail = 'john@doe.com';
        $currentPassword = 'password';

        /** @var AttributeInterface $varcharCustomCustomerAttribute */
        $varcharCustomCustomerAttribute = DataFixtureStorageManager::getStorage()->get(
            'varchar_custom_customer_attribute'
        );
        /** @var AttributeInterface $multiselectCustomCustomerAttribute */
        $multiselectCustomCustomerAttribute = DataFixtureStorageManager::getStorage()->get(
            'multiselect_custom_customer_attribute'
        );
        /** @var AttributeOptionInterface $multiselectCustomCustomerAttributeOption1 */
        $multiselectCustomCustomerAttributeOption1 = DataFixtureStorageManager::getStorage()->get(
            'multiselect_custom_customer_attribute_option_1'
        );
        /** @var AttributeOptionInterface $multiselectCustomCustomerAttributeOption2 */
        $multiselectCustomCustomerAttributeOption2 = DataFixtureStorageManager::getStorage()->get(
            'multiselect_custom_customer_attribute_option_2'
        );
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $response = $this->graphQlQuery(
            self::CUSTOM_ATTRIBUTES_QUERY,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        $this->assertEquals(
            [
                'customer' => [
                    'firstname' => $customer->getFirstname(),
                    'lastname' => $customer->getLastname(),
                    'email' => $customer->getEmail(),
                    'addresses' => [
                        [
                            'country_id' => 'US',
                            'custom_attributesV2' => []
                        ]
                    ],
                    'custom_attributes' => [
                        [
                            'uid' => $this->eavUid->encode(
                                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                                $varcharCustomCustomerAttribute->getAttributeCode()
                            ),
                            'code' => $varcharCustomCustomerAttribute->getAttributeCode(),
                            'value' => '42'
                        ],
                        [
                            'uid' => $this->eavUid->encode(
                                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                                $multiselectCustomCustomerAttribute->getAttributeCode()
                            ),
                            'code' => $multiselectCustomCustomerAttribute->getAttributeCode(),
                            'selected_options' => [
                                [
                                    'uid' => $this->uid->encode($multiselectCustomCustomerAttributeOption1->getValue()),
                                    'label' => $multiselectCustomCustomerAttributeOption1->getLabel(),
                                    'value' => $multiselectCustomCustomerAttributeOption1->getValue(),
                                ],
                                [
                                    'uid' => $this->uid->encode($multiselectCustomCustomerAttributeOption2->getValue()),
                                    'label' => $multiselectCustomCustomerAttributeOption2->getLabel(),
                                    'value' => $multiselectCustomCustomerAttributeOption2->getValue(),
                                ],
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
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_set_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'frontend_input' => 'multiselect',
                'source_model' => Table::class,
                'backend_model' => ArrayBackend::class,
                'attribute_code' => 'labels',
                'attribute_group_id' => 1,
            ],
            'multiselect_custom_customer_address_attribute'
        ),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => 'planet',
                'sort_order' => 1,
                'attribute_set_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_group_id' => 1,
            ],
            'varchar_custom_customer_address_attribute'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => '$multiselect_custom_customer_address_attribute.attribute_code$',
                'label' => 'far',
                'sort_order' => 10
            ],
            'multiselect_custom_customer_address_attribute_option_1'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => '$multiselect_custom_customer_address_attribute.attribute_code$',
                'sort_order' => 20,
                'label' => 'foreign',
                'is_default' => true
            ],
            'multiselect_custom_customer_address_attribute_option_2'
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
                            [
                                'attribute_code' => 'planet',
                                'value' => 'Earth'
                            ],
                            [
                                'attribute_code' => 'labels',
                                'selected_options' => [
                                    ['value' => '$multiselect_custom_customer_address_attribute_option_1.value$'],
                                    ['value' => '$multiselect_custom_customer_address_attribute_option_2.value$']
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'customer'
        ),
    ]
    public function testGetCustomAddressAttributes()
    {
        $currentEmail = 'john@doe.com';
        $currentPassword = 'password';

        /** @var AttributeInterface $varcharCustomCustomerAddressAttribute */
        $varcharCustomCustomerAddressAttribute = DataFixtureStorageManager::getStorage()->get(
            'varchar_custom_customer_address_attribute'
        );
        /** @var AttributeInterface $multiselectCustomCustomerAddressAttribute */
        $multiselectCustomCustomerAddressAttribute = DataFixtureStorageManager::getStorage()->get(
            'multiselect_custom_customer_address_attribute'
        );
        /** @var AttributeOptionInterface $multiselectCustomCustomerAddressAttributeOption1 */
        $multiselectCustomCustomerAddressAttributeOption1 = DataFixtureStorageManager::getStorage()->get(
            'multiselect_custom_customer_address_attribute_option_1'
        );
        /** @var AttributeOptionInterface $multiselectCustomCustomerAddressAttributeOption2 */
        $multiselectCustomCustomerAddressAttributeOption2 = DataFixtureStorageManager::getStorage()->get(
            'multiselect_custom_customer_address_attribute_option_2'
        );
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $response = $this->graphQlQuery(
            self::CUSTOM_ATTRIBUTES_QUERY,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

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
                                    'uid' => $this->eavUid->encode(
                                        AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                                        $varcharCustomCustomerAddressAttribute->getAttributeCode()
                                    ),
                                    'code' => $varcharCustomCustomerAddressAttribute->getAttributeCode(),
                                    'value' => 'Earth'
                                ],
                                [
                                    'uid' => $this->eavUid->encode(
                                        AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                                        $multiselectCustomCustomerAddressAttribute->getAttributeCode()
                                    ),
                                    'code' => $multiselectCustomCustomerAddressAttribute->getAttributeCode(),
                                    'selected_options' => [
                                        [
                                            'uid' => $this->uid->encode(
                                                $multiselectCustomCustomerAddressAttributeOption1->getValue()
                                            ),
                                            'label' => $multiselectCustomCustomerAddressAttributeOption1->getLabel(),
                                            'value' => $multiselectCustomCustomerAddressAttributeOption1->getValue(),
                                        ],
                                        [
                                            'uid' => $this->uid->encode(
                                                $multiselectCustomCustomerAddressAttributeOption2->getValue()
                                            ),
                                            'label' => $multiselectCustomCustomerAddressAttributeOption2->getLabel(),
                                            'value' => $multiselectCustomCustomerAddressAttributeOption2->getValue(),
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'custom_attributes' => []
                ]
            ],
            $response
        );
    }
}
