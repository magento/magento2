<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\AddressMetadataInterface;
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
 * Tests for create customer address with custom attributes V2
 */
#[
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'attribute_set_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'attribute_group_id' => 1,
            'attribute_code' => 'simple_attribute',
            'sort_order' => 2,
            'is_required' => 1,
            'frontend_label' => 'simple_attribute'
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
        ],
        'customer'
    )
]
class CreateCustomerAddressWithCustomAttributesV2Test extends GraphQlAbstract
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
     * @return void
     * @throws LocalizedException
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
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateCustomerAddressWithCustomAttributesV2()
    {
        $response = $this->graphQlMutation(
            $this->getQuery(
                $this->simple_attribute->getAttributeCode(),
                "brand new customer address value",
                $this->multiselect_attribute->getAttributeCode(),
                $this->option2->getValue() . "," . $this->option3->getValue()
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $this->currentPassword)
        );

        $this->assertEquals(
            [
                'createCustomerAddress' =>
                    [
                        'region' => [
                            'region' => 'Arizona',
                            'region_code' => 'AZ'
                        ],
                        'country_code' => 'US',
                        'street' => [
                            '123 Main Street'
                        ],
                        'telephone' => '7777777777',
                        'postcode' => '77777',
                        'city' => 'Phoenix',
                        'default_shipping' => true,
                        'default_billing' => false,
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
                                        'value' => 'brand new customer address value'
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
    public function testAttemptToCreateCustomerAddressPassingNonExistingOption()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Attribute multiselect_attribute does not contain option with Id 1345");

        $this->graphQlMutation(
            $this->getQuery(
                $this->simple_attribute->getAttributeCode(),
                "brand new customer address value",
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
     */
    public function testAttemptToCreateCustomerAddressNonPassingRequiredCustomAttribute()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("\"simple_attribute\" is a required value.");

        $query = <<<QUERY
mutation {
  createCustomerAddress(input: {
    region: {
      region_id: 4
      region: "Arizona"
      region_code: "AZ"
    }
    country_code: US
    street: ["123 Main Street"]
    telephone: "7777777777"
    postcode: "77777"
    city: "Phoenix"
    firstname: "Bob"
    lastname: "Loblaw"
    default_shipping: true
    default_billing: false
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
            $query,
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
    public function testAttemptToCreateCustomerAddressPassingSelectedOptionsToDeprecatedCustomAttributes()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Field \"selected_options\" is not defined by type \"CustomerAddressAttributeInput\""
        );

        $query = <<<QUERY
mutation {
  createCustomerAddress(input: {
    region: {
      region_id: 4
      region: "Arizona"
      region_code: "AZ"
    }
    country_code: US
    street: ["123 Main Street"]
    telephone: "7777777777"
    postcode: "77777"
    city: "Phoenix"
    firstname: "Bob"
    lastname: "Loblaw"
    default_shipping: true
    default_billing: false
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
                $this->multiselect_attribute->getAttributeCode(),
                $this->option2->getValue() . "," . $this->option3->getValue()
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $this->currentPassword)
        );
    }

    /**
     * @param $attributeCode
     * @param $attributeValue
     * @param $selectAttributeCode
     * @param $selectAttributeCode
     *
     * @return string
     */
    private function getQuery($attributeCode, $attributeValue, $selectAttributeCode, $selectAttributeValue)
    {
        $query = <<<QUERY
mutation {
  createCustomerAddress(input: {
    region: {
      region_id: 4
      region: "Arizona"
      region_code: "AZ"
    }
    country_code: US
    street: ["123 Main Street"]
    telephone: "7777777777"
    postcode: "77777"
    city: "Phoenix"
    firstname: "Bob"
    lastname: "Loblaw"
    default_shipping: true
    default_billing: false
    custom_attributesV2: [
      {
        attribute_code: "$attributeCode"
        value: "$attributeValue"
      },
      {
        attribute_code: "$selectAttributeCode"
        value: "$selectAttributeValue"
        selected_options: []
      }
    ]
  }) {
    region {
      region
      region_code
    }
    country_code
    street
    telephone
    postcode
    city
    default_shipping
    default_billing
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

        return $query;
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
