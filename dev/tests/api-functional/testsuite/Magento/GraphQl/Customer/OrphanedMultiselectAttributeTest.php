<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Test\Fixture\AttributeOption;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test that customers and addresses with orphaned multiselect attribute values can be saved via GraphQL
 */
class OrphanedMultiselectAttributeTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $customerAddressRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->customerRegistry = $objectManager->get(CustomerRegistry::class);
        $this->customerAddressRepository = $objectManager->get(AddressRepositoryInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test updating customer and address with orphaned multiselect attributes via GraphQL
     */
    #[
        DbIsolation(false),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_group_id' => 1,
                'attribute_code' => 'test_gql_customer_multi',
                'frontend_input' => 'multiselect',
                'backend_type' => 'varchar',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'used_in_forms' => ['adminhtml_customer', 'customer_account_create', 'customer_account_edit']
            ],
            'gql_customer_attr'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$gql_customer_attr.attribute_code$',
                'label' => 'GQL Cust 1',
                'sort_order' => 10
            ],
            'gql_cust_opt1'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$gql_customer_attr.attribute_code$',
                'label' => 'GQL Cust 2',
                'sort_order' => 20
            ],
            'gql_cust_opt2'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$gql_customer_attr.attribute_code$',
                'label' => 'GQL Cust 3',
                'sort_order' => 30
            ],
            'gql_cust_opt3'
        ),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_set_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_group_id' => 1,
                'attribute_code' => 'test_gql_address_multi',
                'frontend_input' => 'multiselect',
                'backend_type' => 'varchar',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'used_in_forms' => ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
            ],
            'gql_address_attr'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => '$gql_address_attr.attribute_code$',
                'label' => 'GQL Addr 1',
                'sort_order' => 10
            ],
            'gql_addr_opt1'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => '$gql_address_attr.attribute_code$',
                'label' => 'GQL Addr 2',
                'sort_order' => 20
            ],
            'gql_addr_opt2'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => '$gql_address_attr.attribute_code$',
                'label' => 'GQL Addr 3',
                'sort_order' => 30
            ],
            'gql_addr_opt3'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'gqlcustomer@orphaned.test',
                'password' => 'Password123!',
                'custom_attributes' => [
                    [
                        'attribute_code' => 'test_gql_customer_multi',
                        'selected_options' => [
                            ['value' => '$gql_cust_opt1.value$'],
                            ['value' => '$gql_cust_opt2.value$'],
                            ['value' => '$gql_cust_opt3.value$']
                        ]
                    ]
                ],
                'addresses' => [
                    [
                        'firstname' => 'Jane',
                        'lastname' => 'Doe',
                        'street' => ['456 GraphQL St'],
                        'city' => 'GQL City',
                        'postcode' => '54321',
                        'country_id' => 'US',
                        'region_id' => 1,
                        'telephone' => '555-5678',
                        'default_billing' => true,
                        'default_shipping' => true,
                        'custom_attributes' => [
                            [
                                'attribute_code' => 'test_gql_address_multi',
                                'selected_options' => [
                                    ['value' => '$gql_addr_opt1.value$'],
                                    ['value' => '$gql_addr_opt2.value$'],
                                    ['value' => '$gql_addr_opt3.value$']
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'gql_customer'
        )
    ]
    public function testUpdateCustomerAndAddressWithOrphanedMultiselectAttributes(): void
    {
        $customer = $this->fixtures->get('gql_customer');
        $customerOption2 = $this->fixtures->get('gql_cust_opt2');
        $addressOption2 = $this->fixtures->get('gql_addr_opt2');

        $customerModel = $this->customerRepository->getById($customer->getId());
        $customerAttrValue = $customerModel->getCustomAttribute('test_gql_customer_multi')->getValue();
        $this->assertStringContainsString(',', $customerAttrValue, 'Customer should have multiple values');

        $addresses = $customerModel->getAddresses();
        $this->assertNotEmpty($addresses);
        $address = reset($addresses);
        $addressAttrValue = $address->getCustomAttribute('test_gql_address_multi')->getValue();
        $this->assertStringContainsString(',', $addressAttrValue, 'Address should have multiple values');

        $token = $this->customerTokenService->createCustomerAccessToken(
            'gqlcustomer@orphaned.test',
            'Password123!'
        );

        $this->deleteAttributeOption('customer', 'test_gql_customer_multi', 'GQL Cust 2');
        $this->deleteAttributeOption('customer_address', 'test_gql_address_multi', 'GQL Addr 2');
        $this->customerRegistry->remove($customer->getId());

        $customerMutation = <<<MUTATION
mutation {
  updateCustomerV2(
    input: {
      firstname: "Updated GraphQL Name"
    }
  ) {
    customer {
      firstname
      email
    }
  }
}
MUTATION;

        $customerResponse = $this->graphQlMutation(
            $customerMutation,
            [],
            '',
            $this->getCustomerAuthHeaders($token)
        );

        $this->assertArrayHasKey('updateCustomerV2', $customerResponse);
        $this->assertEquals('Updated GraphQL Name', $customerResponse['updateCustomerV2']['customer']['firstname']);

        $this->customerRegistry->remove($customer->getId());
        $updatedCustomer = $this->customerRepository->getById($customer->getId());
        $updatedCustomerAttrValue = $updatedCustomer->getCustomAttribute('test_gql_customer_multi')->getValue();
        $this->assertStringNotContainsString($customerOption2->getValue(), $updatedCustomerAttrValue);

        $updatedAddresses = $updatedCustomer->getAddresses();
        $updatedAddress = reset($updatedAddresses);
        $updatedAddressAttrValue = $updatedAddress->getCustomAttribute('test_gql_address_multi')->getValue();
        $this->assertStringNotContainsString($addressOption2->getValue(), $updatedAddressAttrValue);
    }

    /**
     * Get customer authentication headers
     *
     * @param string $token
     * @return array
     */
    private function getCustomerAuthHeaders(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Delete an attribute option by label
     *
     * @param string $entityType
     * @param string $attributeCode
     * @param string $optionLabel
     * @return void
     * @throws NoSuchEntityException
     */
    private function deleteAttributeOption(string $entityType, string $attributeCode, string $optionLabel): void
    {
        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        $options = $attribute->getOptions();

        foreach ($options as $option) {
            if ($option->getLabel() === $optionLabel && $option->getValue()) {
                $deletedOptionValue = $option->getValue();

                $connection = Bootstrap::getObjectManager()
                    ->get(ResourceConnection::class)
                    ->getConnection();
                $connection->delete(
                    $connection->getTableName('eav_attribute_option'),
                    ['option_id = ?' => $deletedOptionValue]
                );
                $connection->delete(
                    $connection->getTableName('eav_attribute_option_value'),
                    ['option_id = ?' => $deletedOptionValue]
                );
                break;
            }
        }
    }
}
