<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\Attribute;
use Magento\Catalog\Test\Fixture\MultiselectAttribute;
use Magento\Catalog\Test\Fixture\SelectAttribute;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Test\Fixture\AttributeOption as AttributeOptionFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for custom_attributesV2 (items & error) on cart.itemsV2.items.product
 */
class CartItemCustomAttributeTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => 'product_custom_attribute',
                'is_visible_on_front' => true
            ],
            'varchar_custom_attribute'
        ),
        DataFixture(
            MultiselectAttribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'source_model' => Table::class,
                'backend_model' => ArrayBackend::class,
                'attribute_code' => 'product_custom_attribute_multiselect',
                'is_visible_on_front' => true
            ],
            'multiselect_custom_attribute'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => '$multiselect_custom_attribute.attribute_code$',
                'label' => 'red',
                'sort_order' => 20
            ],
            'multiselect_custom_attribute_option_1'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => '$multiselect_custom_attribute.attribute_code$',
                'sort_order' => 10,
                'label' => 'white',
                'is_default' => true
            ],
            'multiselect_custom_attribute_option_2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'custom_attributes' => [
                    [
                        'attribute_code' => '$varchar_custom_attribute.attribute_code$',
                        'value' => ''
                    ],
                    [
                        'attribute_code' => '$multiselect_custom_attribute.attribute_code$',
                        'selected_options' => [],
                    ],
                ],
            ],
            'product'
        ),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testEmptyErrorsOnCartItemCustomAttributeWithEmptyValue(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $productName = $this->fixtures->get('product')->getName();
        $cartQuery = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($cartQuery);
        self::assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            0 => [
                                'product' => [
                                    'name' => $productName,
                                    'custom_attributesV2' => [
                                        'items' => [],
                                        'errors' => []
                                    ]
                                ]
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
            Attribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => 'product_custom_attribute',
                'is_comparable' => 1,
                'is_visible_on_front' => 1
            ],
            'varchar_custom_attribute'
        ),
        DataFixture(
            MultiselectAttribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'source_model' => Table::class,
                'backend_model' => ArrayBackend::class,
                'attribute_code' => 'product_custom_attribute_multiselect',
                'is_visible_on_front' => 1
            ],
            'multiselect_custom_attribute'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => '$multiselect_custom_attribute.attribute_code$',
                'label' => 'red',
                'sort_order' => 20
            ],
            'multiselect_custom_attribute_option_1'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => '$multiselect_custom_attribute.attribute_code$',
                'sort_order' => 10,
                'label' => 'white',
                'is_default' => true
            ],
            'multiselect_custom_attribute_option_2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'custom_attributes' => [
                    [
                        'attribute_code' => '$varchar_custom_attribute.attribute_code$',
                        'value' => 'test value'
                    ],
                    [
                        'attribute_code' => '$multiselect_custom_attribute.attribute_code$',
                        'selected_options' => [
                            ['value' => '$multiselect_custom_attribute_option_1.value$'],
                            ['value' => '$multiselect_custom_attribute_option_2.value$']
                        ],
                    ],
                ],
            ],
            'product'
        ),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testEmptyErrorsOnCartItemCustomAttributeWithNonEmptyValue(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $productName = $this->fixtures->get('product')->getName();
        $multiselectCustomAttrOption1 = $this->fixtures->get('multiselect_custom_attribute_option_1')->getValue();
        $multiselectCustomAttrOption2 = $this->fixtures->get('multiselect_custom_attribute_option_2')->getValue();
        $cartQuery = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($cartQuery);
        self::assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            0 => [
                                'product' => [
                                    'name' => $productName,
                                    'custom_attributesV2' => [
                                        'items' => [
                                            0 => [
                                                'code' => 'product_custom_attribute',
                                                'value' => 'test value'
                                            ],
                                            1 => [
                                                'code' => 'product_custom_attribute_multiselect',
                                                'selected_options' => [
                                                    0 => [
                                                        'value' => $multiselectCustomAttrOption2,
                                                        'label' => 'white'
                                                    ],
                                                    1 => [
                                                        'value' => $multiselectCustomAttrOption1,
                                                        'label' => 'red'
                                                    ]
                                                ]
                                            ]
                                        ],
                                        'errors' => []
                                    ]
                                ]
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
            SelectAttribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'source_model' => Table::class,
                'backend_model' => ArrayBackend::class,
                'attribute_code' => 'product_custom_attribute_select',
                'is_visible_on_front' => true
            ],
            'select_custom_attribute'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => '$select_custom_attribute.attribute_code$',
                'label' => 'red',
                'sort_order' => 20
            ],
            'select_custom_attribute_option_1'
        ),
        DataFixture(
            AttributeOptionFixture::class,
            [
                'entity_type' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => '$select_custom_attribute.attribute_code$',
                'sort_order' => 10,
                'label' => 'white',
                'is_default' => true
            ],
            'select_custom_attribute_option_2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'custom_attributes' => [
                    [
                        'attribute_code' => '$select_custom_attribute.attribute_code$',
                        'selected_options' => [
                            ['value' => '0']
                        ],
                    ],
                ],
            ],
            'product'
        ),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testEmptyErrorsOnCartItemCustomAttributeWithNoOptionSelected(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $productName = $this->fixtures->get('product')->getName();
        $cartQuery = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($cartQuery);
        self::assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            0 => [
                                'product' => [
                                    'name' => $productName,
                                    'custom_attributesV2' => [
                                        'items' => [
                                            0 => [
                                                'code' => 'product_custom_attribute_select',
                                                'selected_options' => []
                                            ]
                                        ],
                                        'errors' => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * Returns cart query with product - custom_attributesV2
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
query {
  cart(cart_id: "{$maskedQuoteId}") {
    itemsV2 {
    items {
      product {
        name
        custom_attributesV2(filters: {is_visible_on_front: true}) {
          items {
            code
            ...on AttributeValue {
              value
            }
            ...on AttributeSelectedOptions {
              selected_options {
                value
                label
              }
            }
          }
          errors {
            message
            type
          }
        }
      }
    }
  }
  }
}
QUERY;
    }
}
