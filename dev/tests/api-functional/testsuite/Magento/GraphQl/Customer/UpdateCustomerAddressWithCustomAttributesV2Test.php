<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Test\Fixture\AttributeOption as AttributeOptionFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for update customer address with custom attributes V2
 */
#[
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'attribute_set_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'attribute_group_id' => 1,
            'attribute_code' => 'simple_attribute',
            'sort_order' => 2
        ],
        'simple_attribute',
    ),
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'attribute_set_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
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
            'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'attribute_code' => '$multiselect_attribute.attribute_code$',
            'label' => 'line 1',
            'sort_order' => 20
        ],
        'multiselect_attribute_option1'
    ),
    DataFixture(
        AttributeOptionFixture::class,
        [
            'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'attribute_code' => '$multiselect_attribute.attribute_code$',
            'label' => 'line 2',
            'sort_order' => 30
        ],
        'multiselect_attribute_option2'
    ),
    DataFixture(
        AttributeOptionFixture::class,
        [
            'entity_type' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'attribute_code' => '$multiselect_attribute.attribute_code$',
            'label' => 'line 3',
            'sort_order' => 10
        ],
        'multiselect_attribute_option3'
    ),
    DataFixture(
        Customer::class,
        [
            'email' => 'customer@example.com',
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
                            'attribute_code' => '$simple_attribute.attribute_code$',
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
                ]
            ]
        ],
        'customer'
    )
]
class UpdateCustomerAddressWithCustomAttributesV2Test extends GraphQlAbstract
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
     * @var AttributeMetadataInterface|null
     */
    private $simple_attribute;

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

    /**
     * @var AddressInterface|null
     */
    private $customerAddress;

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);

        $this->simple_attribute = DataFixtureStorageManager::getStorage()->get('simple_attribute');
        $this->multiselect_attribute = DataFixtureStorageManager::getStorage()->get('multiselect_attribute');
        $this->option2 = DataFixtureStorageManager::getStorage()->get('multiselect_attribute_option2');
        $this->option3 = DataFixtureStorageManager::getStorage()->get('multiselect_attribute_option3');
        $this->customer = DataFixtureStorageManager::getStorage()->get('customer');

        $customerAddresses = $this->customer->getAddresses();
        $this->customerAddress = array_shift($customerAddresses);
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testUpdateCustomerAddressWithCustomAttributesV2()
    {
        $query = <<<QUERY
mutation {
  updateCustomerAddress(id: "%s", input: {
    custom_attributesV2: [
      {
          attribute_code: "%s"
          value: "%s"
      },
      {
          attribute_code: "%s"
          value: "%s"
          selected_options: []
      }
  ]
  }) {
    custom_attributesV2 {
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

        $response = $this->graphQlMutation(
            sprintf(
                $query,
                $this->customerAddress->getId(),
                $this->simple_attribute->getAttributeCode(),
                "another simple value",
                $this->multiselect_attribute->getAttributeCode(),
                $this->option2->getValue() . "," . $this->option3->getValue()
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $this->currentPassword)
        );

        $this->assertEquals(
            [
                'updateCustomerAddress' =>
                    [
                        'custom_attributesV2' =>
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
                                        'code' => $this->simple_attribute->getAttributeCode(),
                                        'value' => 'another simple value'
                                    ]
                            ],
                    ],
            ],
            $response
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAttemptToUpdateCustomerAddressPassingNonExistingOption()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Attribute multiselect_attribute does not contain option with Id 1345");

        $query = <<<QUERY
mutation {
  updateCustomerAddress(id: "%s", input: {
    custom_attributesV2: [
      {
        attribute_code: "%s"
        value: "%s"
        selected_options: []
      }
    ]
  }) {
    custom_attributesV2 {
      code
      ... on AttributeValue {
        value
      }
      ... on AttributeSelectedOptions {
        selected_options {
          value
          label
        }
      }
    }
  }
}
QUERY;

        $this->graphQlMutation(
            sprintf(
                $query,
                $this->customerAddress->getId(),
                $this->multiselect_attribute->getAttributeCode(),
                "1345"
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $this->currentPassword)
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAttemptToUpdateCustomerAddressPassingSelectedOptionsToDeprecatedCustomAttributes()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Field \"selected_options\" is not defined by type \"CustomerAddressAttributeInput\""
        );

        $query = <<<QUERY
mutation {
  updateCustomerAddress(id: "%s", input: {
    custom_attributes: [
      {
        attribute_code: "%s"
        value: "%s"
        selected_options: []
      }
    ]
  }) {
    custom_attributes {
      attribute_code
      value
      selected_options {
        value
        label
      }
    }
  }
}
QUERY;

        $this->graphQlMutation(
            sprintf(
                $query,
                $this->customerAddress->getId(),
                $this->multiselect_attribute->getAttributeCode(),
                $this->option2->getValue() . "," . $this->option3->getValue()
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $this->currentPassword)
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
