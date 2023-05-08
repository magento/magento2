<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;

/**
 * Tests for create customer (V2)
 */
class CreateCustomerV2WithCustomAttributesTest extends GraphQlAbstract
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * @throws \Exception
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
            'attribute1'
        ),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'sort_order' => 2,
                'attribute_code' => 'custom_attribute_two',
                'attribute_set_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_group_id' => 1
            ],
            'attribute2'
        )
    ]
    public function testCreateCustomerAccountWithCustomAttributes()
    {
        $query = <<<QUERY
mutation {
    createCustomerV2(
        input: {
            firstname: "Adam"
            lastname: "Smith"
            email: "adam@smith.com"
            password: "test123#"
            custom_attributes: [
                {
                    attribute_code: "custom_attribute_one",
                    value: "value_one"
                },
                {
                    attribute_code: "custom_attribute_two",
                    value: "value_two"
                }
            ]
        }
    ) {
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
                        'firstname' => 'Adam',
                        'lastname' => 'Smith',
                        'email' => 'adam@smith.com',
                        'custom_attributes' =>
                        [
                            0 =>
                            [
                                'uid' => 'Y3VzdG9tZXIvY3VzdG9tX2F0dHJpYnV0ZV9vbmU=',
                                'code' => 'custom_attribute_one',
                                'value' => 'value_one',
                            ],
                            1 =>
                            [
                                'uid' => 'Y3VzdG9tZXIvY3VzdG9tX2F0dHJpYnV0ZV90d28=',
                                'code' => 'custom_attribute_two',
                                'value' => 'value_two',
                            ],
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
        } catch (\Exception $exception) {
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
