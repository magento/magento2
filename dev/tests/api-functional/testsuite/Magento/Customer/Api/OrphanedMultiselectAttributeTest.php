<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Api;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Test\Fixture\AttributeOption;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test that customers and addresses with orphaned multiselect attribute values can be saved
 */
class OrphanedMultiselectAttributeTest extends WebapiAbstract
{
    private const CUSTOMER_RESOURCE_PATH = '/V1/customers/:customerId';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);
        $this->customerRegistry = $objectManager->get(CustomerRegistry::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test that existing customer with orphaned multiselect values can be updated
     */
    #[
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_group_id' => 1,
                'attribute_code' => 'test_customer_multi',
                'frontend_input' => 'multiselect',
                'backend_type' => 'varchar',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'used_in_forms' => ['adminhtml_customer', 'customer_account_create', 'customer_account_edit']
            ],
            'customer_attribute'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$customer_attribute.attribute_code$',
                'label' => 'Customer Opt 1',
                'sort_order' => 10
            ],
            'cust_option1'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$customer_attribute.attribute_code$',
                'label' => 'Customer Opt 2',
                'sort_order' => 20
            ],
            'cust_option2'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$customer_attribute.attribute_code$',
                'label' => 'Customer Opt 3',
                'sort_order' => 30
            ],
            'cust_option3'
        ),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_set_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_group_id' => 1,
                'attribute_code' => 'test_address_multi',
                'frontend_input' => 'multiselect',
                'backend_type' => 'varchar',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'used_in_forms' => ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
            ],
            'address_attribute'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => '$address_attribute.attribute_code$',
                'label' => 'Address Opt 1',
                'sort_order' => 10
            ],
            'addr_option1'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => '$address_attribute.attribute_code$',
                'label' => 'Address Opt 2',
                'sort_order' => 20
            ],
            'addr_option2'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'attribute_code' => '$address_attribute.attribute_code$',
                'label' => 'Address Opt 3',
                'sort_order' => 30
            ],
            'addr_option3'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'custom_attributes' => [
                    [
                        'attribute_code' => 'test_customer_multi',
                        'selected_options' => [
                            ['value' => '$cust_option1.value$'],
                            ['value' => '$cust_option2.value$'],
                            ['value' => '$cust_option3.value$']
                        ]
                    ]
                ],
                'addresses' => [
                    [
                        'firstname' => 'John',
                        'lastname' => 'Doe',
                        'street' => ['123 Test St'],
                        'city' => 'Test City',
                        'postcode' => '12345',
                        'country_id' => 'US',
                        'region_id' => 1,
                        'telephone' => '555-1234',
                        'default_billing' => true,
                        'default_shipping' => true,
                        'custom_attributes' => [
                            [
                                'attribute_code' => 'test_address_multi',
                                'selected_options' => [
                                    ['value' => '$addr_option1.value$'],
                                    ['value' => '$addr_option2.value$'],
                                    ['value' => '$addr_option3.value$']
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'customer'
        )
    ]
    public function testUpdateCustomerAndAddressWithOrphanedMultiselectAttributes(): void
    {
        $customer = $this->fixtures->get('customer');
        $customerOption2 = $this->fixtures->get('cust_option2');
        $addressOption2 = $this->fixtures->get('addr_option2');

        $customerModel = $this->customerRepository->getById($customer->getId());
        $customerAttrValue = $customerModel->getCustomAttribute('test_customer_multi')->getValue();
        $this->assertStringContainsString(',', $customerAttrValue, 'Customer should have multiple values');

        $addresses = $customerModel->getAddresses();
        $this->assertNotEmpty($addresses);
        $address = reset($addresses);
        $addressAttrValue = $address->getCustomAttribute('test_address_multi')->getValue();
        $this->assertStringContainsString(',', $addressAttrValue, 'Address should have multiple values');

        // Delete option2 from both attributes (simulate admin deleting options after customer has them)
        $this->deleteAttributeOption('customer', 'test_customer_multi', 'Customer Opt 2');
        $this->deleteAttributeOption('customer_address', 'test_address_multi', 'Address Opt 2');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace(':customerId', (string)$customer->getId(), self::CUSTOMER_RESOURCE_PATH),
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
        ];

        $customerData = [];
        $customerData['id'] = $customer->getId();
        $customerData['firstname'] = 'Updated Name';

        $addressData = [];
        $addressData['id'] = $address->getId();
        $addressData['city'] = 'Updated City';

        $customerData['addresses'] = [$addressData];
        $requestData = ['customer' => $customerData];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('Updated Name', $response['firstname']);

        $this->customerRegistry->remove($customer->getId()); // Clear registry to force reload
        $updatedCustomer = $this->customerRepository->getById($customer->getId());
        $updatedCustomerAttrValue = $updatedCustomer->getCustomAttribute('test_customer_multi')->getValue();
        $this->assertStringNotContainsString($customerOption2->getValue(), $updatedCustomerAttrValue);

        $updatedAddresses = $updatedCustomer->getAddresses();
        $updatedAddress = reset($updatedAddresses);
        $updatedAddressAttrValue = $updatedAddress->getCustomAttribute('test_address_multi')->getValue();
        $this->assertStringNotContainsString($addressOption2->getValue(), $updatedAddressAttrValue);
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
