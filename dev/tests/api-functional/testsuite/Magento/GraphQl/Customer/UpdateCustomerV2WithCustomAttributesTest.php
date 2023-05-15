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
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for new update customer endpoint
 */
#[
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'sort_order' => 1,
            'attribute_code' => 'custom_attribute_one',
            'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_group_id' => 1
        ],
        'attribute',
    ),
    DataFixture(
        Customer::class,
        [
            'email' => 'customer@example.com',
            'custom_attributes' => [
                [
                    'attribute_code' => '$attribute.attribute_code$',
                    'value' => 'value_one'
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
    private $query = <<<QUERY
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
                uid
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
    private $currentPassword = 'password';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testUpdateCustomerWithCorrectCustomerAttribute(): void
    {
        /** @var AttributeMetadataInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $response = $this->graphQlMutation(
            sprintf(
                $this->query,
                $attribute->getAttributeCode(),
                'new_value_for_attribute'
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
                                                'uid' => 'Y3VzdG9tZXIvY3VzdG9tX2F0dHJpYnV0ZV9vbmU=',
                                                'code' => 'custom_attribute_one',
                                                'value' => 'new_value_for_attribute'
                                            ],
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
                'new_value_for_attribute'
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
                                                'uid' => 'Y3VzdG9tZXIvY3VzdG9tX2F0dHJpYnV0ZV9vbmU=',
                                                'code' => 'custom_attribute_one',
                                                'value' => 'value_one',
                                            ],
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
                $this->query,
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
                $this->query,
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
                $this->query,
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
