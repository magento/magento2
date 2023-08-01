<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Test\Fixture\AttributeOption as AttributeOptionFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;

/**
 * Tests for create customer V2
 */
#[
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_group_id' => 1,
            'attribute_code' => 'random_attribute',
            'sort_order' => 2
        ],
        'random_attribute',
    ),
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_group_id' => 1,
            'source_model' => Table::class,
            'backend_model' => ArrayBackend::class,
            'attribute_code' => 'multiselect_attribute',
            'frontend_input' => 'multiselect',
            'sort_order' => 1
        ],
        'multiselect_attribute',
    ),
    DataFixture(
        AttributeOptionFixture::class,
        [
            'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => '$multiselect_attribute.attribute_code$',
            'label' => 'line 1',
            'sort_order' => 20
        ],
        'multiselect_attribute_option1'
    ),
    DataFixture(
        AttributeOptionFixture::class,
        [
            'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => '$multiselect_attribute.attribute_code$',
            'label' => 'option 2',
            'sort_order' => 30
        ],
        'multiselect_attribute_option2'
    ),
    DataFixture(
        AttributeOptionFixture::class,
        [
            'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => '$multiselect_attribute.attribute_code$',
            'label' => 'option 3',
            'sort_order' => 10
        ],
        'multiselect_attribute_option3'
    )
]
class CreateCustomerV2WithCustomAttributesTest extends GraphQlAbstract
{
    /**
     * @var string
     */
    private $query = <<<QUERY
mutation {
    createCustomerV2(
        input: {
            firstname: "Adam"
            lastname: "Smith"
            email: "adam@smith.com"
            password: "test123#"
            custom_attributes: [
                {
                    attribute_code: "%s"
                    value: "%s"
                }
                {
                    attribute_code: "%s"
                    value: "%s"
                    selected_options: []
                }
            ]
        }
    ) {
        customer {
            firstname
            lastname
            email
            custom_attributes {
                code
                ... on AttributeValue {
                    value
                }
                ... on AttributeSelectedOptions {
                    selected_options {
                        label
                        value
                    }
                }
            }
        }
    }
}
QUERY;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AttributeMetadataInterface|null
     */
    private $random_attribute;

    /**
     * @var AttributeMetadataInterface|null
     */
    private $multiselect_attribute;

    /**
     * @var AttributeOptionInterface|null
     */
    private $option2;

    /**
     * @var AttributeOptionInterface|null
     */
    private $option3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);

        $this->random_attribute = DataFixtureStorageManager::getStorage()->get('random_attribute');
        $this->multiselect_attribute = DataFixtureStorageManager::getStorage()->get('multiselect_attribute');
        $this->option2 = DataFixtureStorageManager::getStorage()->get('multiselect_attribute_option2');
        $this->option3 = DataFixtureStorageManager::getStorage()->get('multiselect_attribute_option3');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCreateCustomerAccountWithCustomAttributes()
    {
        $response = $this->graphQlMutation(
            sprintf(
                $this->query,
                $this->random_attribute->getAttributeCode(),
                'new_value_for_attribute',
                $this->multiselect_attribute->getAttributeCode(),
                $this->option2->getValue() . "," . $this->option3->getValue()
            )
        );

        $this->assertEquals(
            [
                'createCustomerV2' =>
                [
                    'customer' =>
                    [
                        'firstname' => 'Adam',
                        'lastname' => 'Smith',
                        'email' => 'adam@smith.com',
                        'custom_attributes' =>
                        [
                            0 =>
                                [
                                    'code' => $this->multiselect_attribute->getAttributeCode(),
                                    'selected_options' => [
                                        [
                                            'label' => $this->option3->getLabel(),
                                            'value' => $this->option3->getValue()
                                        ],
                                        [
                                            'label' => $this->option2->getLabel(),
                                            'value' => $this->option2->getValue()
                                        ]
                                    ]
                                ],
                            1 =>
                                [
                                    'code' => $this->random_attribute->getAttributeCode(),
                                    'value' => 'new_value_for_attribute',
                                ]
                        ],
                    ],
                ],
            ],
            $response
        );
    }

    public function testCreateCustomerAccountWithNonExistingCustomAttribute()
    {
        $query = <<<QUERY
mutation {
    createCustomerV2(
        input: {
            firstname: "John"
            lastname: "Doe"
            email: "john@doe.com"
            password: "test123#"
            custom_attributes: [
                {
                    attribute_code: "non_existing_custom_attribute",
                    value: "void"
                }
            ]
        }
    ) {
        customer {
            firstname
            lastname
            email
            custom_attributes {
                code
                ... on AttributeValue {
                    value
                }
            }
        }
    }
}
QUERY;
        $response = $this->graphQlMutation($query);
        $this->assertEquals(
            [
                'createCustomerV2' =>
                [
                    'customer' =>
                    [
                        'firstname' => 'John',
                        'lastname' => 'Doe',
                        'email' => 'john@doe.com',
                        'custom_attributes' => []
                    ],
                ],
            ],
            $response
        );
    }

    protected function tearDown(): void
    {
        $email1 = 'adam@smith.com';
        $email2 = 'john@doe.com';
        try {
            $customer1 = $this->customerRepository->get($email1);
            $customer2 = $this->customerRepository->get($email2);
        } catch (Exception $exception) {
            return;
        }

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $this->customerRepository->delete($customer1);
        $this->customerRepository->delete($customer2);
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
        parent::tearDown();
    }
}
