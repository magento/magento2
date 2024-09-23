<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Test\Fixture\AttributeOption as AttributeOptionFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * GraphQl tests for @see \Magento\CustomerGraphQl\Model\Customer\GetCustomer.
 */
#[
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => 'shoe_size',
            'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_group_id' => 1,
            'sort_order' => 2
        ],
        'varchar_customer_attribute'
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
            'sort_order' => 1
        ],
        'multiselect_customer_attribute'
    ),
    DataFixture(
        AttributeOptionFixture::class,
        [
            'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => '$multiselect_customer_attribute.attribute_code$',
            'label' => 'red',
            'sort_order' => 20
        ],
        'multiselect_customer_attribute_option_1'
    ),
    DataFixture(
        AttributeOptionFixture::class,
        [
            'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => '$multiselect_customer_attribute.attribute_code$',
            'sort_order' => 10,
            'label' => 'white',
            'is_default' => true
        ],
        'multiselect_customer_attribute_option_2'
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
                        ['value' => '$multiselect_customer_attribute_option_1.value$'],
                        ['value' => '$multiselect_customer_attribute_option_2.value$']
                    ],
                ],
            ],
        ],
        'customer'
    ),
]
class GetCustomerWithCustomAttributesTest extends GraphQlAbstract
{
    /**
     * @var string
     */
    private $currentPassword = 'password';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AttributeInterface|null
     */
    private $varcharCustomerAttribute;

    /**
     * @var AttributeInterface|null
     */
    private $multiselectCustomerAttribute;

    /**
     * @var AttributeOptionInterface|null
     */
    private $multiselectCustomerAttributeOption1;

    /**
     * @var AttributeOptionInterface|null
     */
    private $multiselectCustomerAttributeOption2;

    /**
     * @var CustomerInterface|null
     */
    private $customer;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);

        $this->varcharCustomerAttribute = DataFixtureStorageManager::getStorage()->get(
            'varchar_customer_attribute'
        );
        $this->multiselectCustomerAttribute = DataFixtureStorageManager::getStorage()->get(
            'multiselect_customer_attribute'
        );
        $this->multiselectCustomerAttributeOption1 = DataFixtureStorageManager::getStorage()->get(
            'multiselect_customer_attribute_option_1'
        );
        $this->multiselectCustomerAttributeOption2 = DataFixtureStorageManager::getStorage()->get(
            'multiselect_customer_attribute_option_2'
        );
        $this->customer = DataFixtureStorageManager::getStorage()->get('customer');
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    public function testGetCustomAttributes()
    {
        $query = <<<QUERY
query {
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
QUERY;

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $this->currentPassword)
        );

        $this->assertEquals(
            [
                'customer' => [
                    'firstname' => $this->customer->getFirstname(),
                    'lastname' => $this->customer->getLastname(),
                    'email' => $this->customer->getEmail(),
                    'custom_attributes' => [
                        [
                            'code' => $this->multiselectCustomerAttribute->getAttributeCode(),
                            'selected_options' => [
                                [
                                    'label' => $this->multiselectCustomerAttributeOption2->getLabel(),
                                    'value' => $this->multiselectCustomerAttributeOption2->getValue(),
                                ],
                                [
                                    'label' => $this->multiselectCustomerAttributeOption1->getLabel(),
                                    'value' => $this->multiselectCustomerAttributeOption1->getValue(),
                                ]
                            ]
                        ],
                        [
                            'code' => $this->varcharCustomerAttribute->getAttributeCode(),
                            'value' => '42'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetFilteredCustomAttributes()
    {
        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
        custom_attributes(attributeCodes: ["%s"]) {
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
QUERY;

        $response = $this->graphQlQuery(
            sprintf($query, $this->multiselectCustomerAttribute->getAttributeCode()),
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $this->currentPassword)
        );

        $this->assertEquals(
            [
                'customer' => [
                    'firstname' => $this->customer->getFirstname(),
                    'lastname' => $this->customer->getLastname(),
                    'email' => $this->customer->getEmail(),
                    'custom_attributes' => [
                        [
                            'code' => $this->multiselectCustomerAttribute->getAttributeCode(),
                            'selected_options' => [
                                [
                                    'label' => $this->multiselectCustomerAttributeOption2->getLabel(),
                                    'value' => $this->multiselectCustomerAttributeOption2->getValue(),
                                ],
                                [
                                    'label' => $this->multiselectCustomerAttributeOption1->getLabel(),
                                    'value' => $this->multiselectCustomerAttributeOption1->getValue(),
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }
}
