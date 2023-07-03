<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Test\Fixture\AttributeOption as AttributeOptionFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for update customer V2
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
    ),
    DataFixture(
        Customer::class,
        [
            'email' => 'customer@example.com',
            'custom_attributes' => [
                [
                    'attribute_code' => '$random_attribute.attribute_code$',
                    'value' => 'value_one'
                ],
                [
                    'attribute_code' => '$multiselect_attribute.attribute_code$',
                    'selected_options' => [
                        [
                            'value' => '$multiselect_attribute_option1.value$'
                        ],
                        [
                            'value' => '$multiselect_attribute_option2.value$'
                        ]
                    ]
                ]
            ]
        ],
        'customer'
    )
]
class UpdateCustomerV2WithCustomAttributesTest extends GraphQlAbstract
{
    /**
     * @var string
     */
    private $simpleQuery = <<<QUERY
mutation {
    updateCustomerV2(
        input: {
            custom_attributes: [
                {
                    attribute_code: "%s",
                    value: "%s"
                }
            ]
        }
    ) {
        customer {
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

    /**
     * @var string
     */
    private $query = <<<QUERY
mutation {
    updateCustomerV2(
        input: {
            custom_attributes: [
                {
                    attribute_code: "%s",
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
     * @var string
     */
    private $currentPassword = 'password';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

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

    /**
     * @var CustomerInterface|null
     */
    private $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);

        $this->random_attribute = DataFixtureStorageManager::getStorage()->get('random_attribute');
        $this->multiselect_attribute = DataFixtureStorageManager::getStorage()->get('multiselect_attribute');
        $this->option2 = DataFixtureStorageManager::getStorage()->get('multiselect_attribute_option2');
        $this->option3 = DataFixtureStorageManager::getStorage()->get('multiselect_attribute_option3');
        $this->customer = DataFixtureStorageManager::getStorage()->get('customer');
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testUpdateCustomerWithCorrectCustomerAttribute(): void
    {
        $response = $this->graphQlMutation(
            sprintf(
                $this->query,
                $this->random_attribute->getAttributeCode(),
                'new_value_for_attribute',
                $this->multiselect_attribute->getAttributeCode(),
                $this->option2->getValue() . "," . $this->option3->getValue()
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $this->currentPassword)
        );

        $this->assertEquals(
            [
                'updateCustomerV2' =>
                    [
                        'customer' =>
                            [
                                'email' => 'customer@example.com',
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
                                                'value' => 'new_value_for_attribute'
                                            ]
                                    ],
                            ],
                    ],
            ],
            $response
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testAttemptToUpdateCustomerPassingNonExistingCustomerAttribute(): void
    {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $response = $this->graphQlMutation(
            sprintf(
                $this->query,
                'non_existing_custom_attribute',
                'new_value_for_attribute',
                $this->multiselect_attribute->getAttributeCode(),
                $this->option2->getValue() . "," . $this->option3->getValue()
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail(), $this->currentPassword)
        );

        $this->assertEquals(
            [
                'updateCustomerV2' =>
                    [
                        'customer' =>
                            [
                                'email' => 'customer@example.com',
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
                                                'value' => 'value_one'
                                            ]
                                    ],
                            ],
                    ],
            ],
            $response
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'sort_order' => 1,
                'attribute_code' => 'date_attribute',
                'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_group_id' => 1,
                'frontend_input' => 'date',
                'backend_type' => 'datetime',
                'input_filter' => 'date',
            ],
            'date_attribute',
        ),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$date_attribute.attribute_code$',
                        'value' => '2023-03-22 00:00:00'
                    ]
                ]
            ],
            'customer'
        )
    ]
    public function testAttemptToUpdateCustomerAttributeWithInvalidDataType(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid date");

        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        /** @var AttributeMetadataInterface $date_attribute */
        $date_attribute = DataFixtureStorageManager::getStorage()->get('date_attribute');

        $this->graphQlMutation(
            sprintf(
                $this->simpleQuery,
                $date_attribute->getAttributeCode(),
                'this_is_an_invalid_value_for_dates'
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail(), $this->currentPassword)
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_group_id' => 1,
                'attribute_code' => 'date_range_attribute',
                'frontend_input' => 'date',
                'frontend_class' => 'Magento\Eav\Model\Entity\Attribute\Frontend\Datetime',
                'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
                'backend_type' => 'datetime',
                'input_filter' => 'date',
                'validate_rules' =>
                    '{"date_range_min":1679443200,"date_range_max":1679875200,"input_validation":"date"}'
            ],
            'date_range_attribute',
        ),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$date_range_attribute.attribute_code$',
                        'value' => '1679443200'
                    ]
                ]
            ],
            'customer'
        )
    ]
    public function testAttemptToUpdateCustomerAttributeWithInvalidValue(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Please enter a valid date between 22/03/2023 and 27/03/2023");

        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        /** @var AttributeMetadataInterface $date_range */
        $date_range = DataFixtureStorageManager::getStorage()->get('date_range_attribute');

        $this->graphQlMutation(
            sprintf(
                $this->simpleQuery,
                $date_range->getAttributeCode(),
                '1769443200'
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail(), $this->currentPassword)
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'sort_order' => 1,
                'attribute_code' => 'boolean_attribute',
                'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_group_id' => 1,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'boolean_attribute',
        ),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$boolean_attribute.attribute_code$',
                        'value' => '1'
                    ]
                ]
            ],
            'customer'
        )
    ]
    public function testAttemptToUpdateBooleanCustomerAttributeWithInvalidValue(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Attribute boolean_attribute does not contain option with Id 3");

        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        /** @var AttributeMetadataInterface $date_attribute */
        $date_attribute = DataFixtureStorageManager::getStorage()->get('boolean_attribute');

        $this->graphQlMutation(
            sprintf(
                $this->simpleQuery,
                $date_attribute->getAttributeCode(),
                "3"
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail(), $this->currentPassword)
        );
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
}
