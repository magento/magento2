<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\DataObject;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test products query output
 */
class ProductViewTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryAllFieldsSimpleProduct()
    {
        $productSku = 'simple';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            attribute_set_id
            country_of_manufacture
            created_at
            gift_message_available
            id
            categories {
               name
               url_path
               available_sort_by
               level
            }
            image { url, label }
            meta_description
            meta_keyword
            meta_title
            media_gallery_entries
            {
                disabled
                file
                id
                label
                media_type
                position
                types
                content
                {
                    base64_encoded_data
                    type
                    name
                }
                video_content
                {
                    media_type
                    video_description
                    video_metadata
                    video_provider
                    video_title
                    video_url
                }
            }
            name
            new_from_date
            new_to_date
            options_container
            ... on CustomizableProductInterface {
              options {
                title
                required
                sort_order
                option_id
                ... on CustomizableFieldOption {
                  product_sku
                  field_option: value {
                    sku
                    price
                    price_type
                    max_characters
                  }
                }
                ... on CustomizableAreaOption {
                  product_sku
                  area_option: value {
                    sku
                    price
                    price_type
                    max_characters
                  }
                }
                ... on CustomizableDateOption {
                  product_sku
                  date_option: value {
                    sku
                    price
                    price_type
                  }
                }
                ... on CustomizableDropDownOption {
                  drop_down_option: value {
                    option_type_id
                    sku
                    price
                    price_type
                    title
                    sort_order
                  }
                }
                ... on CustomizableRadioOption {
                  radio_option: value {
                    option_type_id
                    sku
                    price
                    price_type
                    title
                    sort_order
                  }
                }
                ... on CustomizableCheckboxOption {
                  checkbox_option: value {
                    option_type_id
                    sku
                    price
                    price_type
                    title
                    sort_order
                  }
                }
                ... on CustomizableMultipleOption {
                  multiple_option: value {
                    option_type_id
                    sku
                    price
                    price_type
                    title
                    sort_order
                  }
                }
                ...on CustomizableFileOption {
                    product_sku
                    file_option: value {
                      sku
                      price
                      price_type
                      file_extension
                      image_size_x
                      image_size_y
                    }
                  }
              }
            }
            price {
              minimalPrice {
                amount {
                  value
                  currency
                }
                adjustments {
                  amount {
                    value
                    currency
                  }
                  code
                  description
                }
              }
              maximalPrice {
                amount {
                  value
                  currency
                }
                adjustments {
                  amount {
                    value
                    currency
                  }
                  code
                  description
                }
              }
              regularPrice {
                amount {
                  value
                  currency
                }
                adjustments {
                  amount {
                    value
                    currency
                  }
                  code
                  description
                }
              }
            }
            product_links
            {
                link_type
                linked_product_sku
                linked_product_type
                position
                sku
            }
            sku
            small_image{ url, label }
            thumbnail { url, label }
            special_from_date
            special_price
            special_to_date
            swatch_image            
            tier_price
            tier_prices
            {
                customer_group_id
                percentage_value
                qty
                value
                website_id
            }
            type_id
            updated_at
            url_key
            url_path
            canonical_url
            websites { id name code sort_order default_group_id is_default }
            ... on PhysicalProductInterface {
                weight
            }
        }
    }
}
QUERY;

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $customerToken = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $responseObject = new DataObject($response);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertCount(1, $response['products']['items']);
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertBaseFields($product, $response['products']['items'][0]);
        $this->assertEavAttributes($product, $response['products']['items'][0]);
        $this->assertOptions($product, $response['products']['items'][0]);
        $this->assertArrayHasKey('websites', $response['products']['items'][0]);
        $this->assertWebsites($product, $response['products']['items'][0]['websites']);
        self::assertEquals(
            'Movable Position 2',
            $responseObject->getData('products/items/0/categories/0/name')
        );
        self::assertEquals(
            'Filter category',
            $responseObject->getData('products/items/0/categories/1/name')
        );
        //canonical_url will be null unless the admin setting catalog/seo/product_canonical_tag is turned ON
        self::assertNull($responseObject->getData('products/items/0/canonical_url'));
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_media_gallery_entries.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryMediaGalleryEntryFieldsSimpleProduct()
    {
        $productSku = 'simple';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items{
            attribute_set_id
            categories
            {
                id
            }
            country_of_manufacture
            created_at
            gift_message_available
            id
            image {url, label}
            meta_description
            meta_keyword
            meta_title
            media_gallery_entries
            {
                disabled
                file
                id
                label
                media_type
                position
                types
                content
                {
                    base64_encoded_data
                    type
                    name
                }
                video_content
                {
                    media_type
                    video_description
                    video_metadata
                    video_provider
                    video_title
                    video_url
                }
            }
            name
            new_from_date
            new_to_date
            options_container
            ... on CustomizableProductInterface {
              field_options: options {
                title
                required
                sort_order
                option_id
                ... on CustomizableFieldOption {
                  product_sku
                  field_option: value {
                    sku
                    price
                    price_type
                    max_characters
                  }
                }
                ... on CustomizableAreaOption {
                  product_sku
                  area_option: value {
                    sku
                    price
                    price_type
                    max_characters
                  }
                }
                ... on CustomizableDateOption {
                  product_sku
                  date_option: value {
                    sku
                    price
                    price_type
                  }
                }
                ... on CustomizableDropDownOption {
                  drop_down_option: value {
                    option_type_id
                    sku
                    price
                    price_type
                    title
                  }
                }
                ... on CustomizableRadioOption {
                  radio_option: value {
                    option_type_id
                    sku
                    price
                    price_type
                    title
                  }
                }
                ...on CustomizableFileOption {
                    product_sku
                    file_option: value {
                      sku
                      price
                      price_type
                      file_extension
                      image_size_x
                      image_size_y
                    }
                  }
              }
            }
            price {
              minimalPrice {
                amount {
                  value
                  currency
                }
                adjustments {
                  amount {
                    value
                    currency
                  }
                  code
                  description
                }
              }
              maximalPrice {
                amount {
                  value
                  currency
                }
                adjustments {
                  amount {
                    value
                    currency
                  }
                  code
                  description
                }
              }
              regularPrice {
                amount {
                  value
                  currency
                }
                adjustments {
                  amount {
                    value
                    currency
                  }
                  code
                  description
                }
              }
            }
            product_links
            {
                link_type
                linked_product_sku
                linked_product_type
                position
                sku
            }
            sku
            small_image { url, label }
            special_from_date
            special_price
            special_to_date
            swatch_image
            thumbnail { url, label }
            tier_price
            tier_prices
            {
                customer_group_id
                percentage_value
                qty
                value
                website_id
            }
            type_id
            updated_at
            url_key
            url_path
            websites { id name code sort_order default_group_id is_default }
            ... on PhysicalProductInterface {
                weight
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertCount(1, $response['products']['items']);
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertMediaGalleryEntries($product, $response['products']['items'][0]);
        $this->assertArrayHasKey('websites', $response['products']['items'][0]);
        $this->assertWebsites($product, $response['products']['items'][0]['websites']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute.php
     */
    public function testQueryCustomAttributeField()
    {
        if (!$this->cleanCache()) {
            $this->fail('Cache could not be cleaned properly.');
        }
        $prductSku = 'simple';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$prductSku}"}})
    {
        items
        {
            attribute_code_custom
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertCount(1, $response['products']['items']);
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertCustomAttribute($response['products']['items'][0]);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related.php
     */
    public function testProductLinks()
    {
        $productSku = 'simple_with_cross';

        $query = <<<QUERY
       {
           products(filter: {sku: {eq: "{$productSku}"}})
           {
               items {
                   attribute_set_id
                   type_id
                   product_links
                   {
                       link_type
                       linked_product_sku
                       linked_product_type
                       position
                       sku
                   }
               }
           }
       }
QUERY;

        $response = $this->graphQlQuery($query);
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);
        $this->assertNotNull($response['products']['items'][0]['product_links'], "product_links must not be null");
        $this->assertProductLinks($product, $response['products']['items'][0]['product_links'][0]);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_list.php
     */
    public function testProductPrices()
    {
        $firstProductSku = 'simple-249';
        $secondProductSku = 'simple-156';
        $query = <<<QUERY
       {
           products(filter: {price: {from: "150.0", to: "250.0"}})
           {
               items {
                   attribute_set_id
                   created_at
                   id
                   name
                   price {
                      minimalPrice {
                        amount {
                          value
                          currency
                        }
                        adjustments {
                          amount {
                            value
                            currency
                          }
                          code
                          description
                        }
                      }
                      maximalPrice {
                        amount {
                          value
                          currency
                        }
                        adjustments {
                          amount {
                            value
                            currency
                          }
                          code
                          description
                        }
                      }
                      regularPrice {
                        amount {
                          value
                          currency
                        }
                        adjustments {
                          amount {
                            value
                            currency
                          }
                          code
                          description
                        }
                      }
                    }
                   sku
                   type_id
                   updated_at
                   ... on PhysicalProductInterface {
                        weight
                   }
               }
           }
       }
QUERY;

        $response = $this->graphQlQuery($query);
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $firstProduct = $productRepository->get($firstProductSku, false, null, true);
        $secondProduct = $productRepository->get($secondProductSku, false, null, true);
        self::assertNotNull($response['products']['items'][0]['price'], "price must be not null");
        self::assertCount(2, $response['products']['items']);

        // by default sort order is: "newest id first"
        $this->assertBaseFields($secondProduct, $response['products']['items'][0]);
        $this->assertBaseFields($firstProduct, $response['products']['items'][1]);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertMediaGalleryEntries($product, $actualResponse)
    {
        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        $this->assertCount(1, $mediaGalleryEntries, "Precondition failed, incorrect number of media gallery entries.");
        $this->assertIsArray(
            [$actualResponse['media_gallery_entries']],
            "Media galleries field must be of an array type."
        );
        $this->assertCount(1, $actualResponse['media_gallery_entries'], "There must be 1 record in media gallery.");
        $mediaGalleryEntry = $mediaGalleryEntries[0];
        $this->assertResponseFields(
            $actualResponse['media_gallery_entries'][0],
            [
                'disabled' => (bool)$mediaGalleryEntry->isDisabled(),
                'file' => $mediaGalleryEntry->getFile(),
                'id' => $mediaGalleryEntry->getId(),
                'label' => $mediaGalleryEntry->getLabel(),
                'media_type' => $mediaGalleryEntry->getMediaType(),
                'position' => $mediaGalleryEntry->getPosition(),
            ]
        );
        $videoContent = $mediaGalleryEntry->getExtensionAttributes()->getVideoContent();
        $this->assertResponseFields(
            $actualResponse['media_gallery_entries'][0]['video_content'],
            [
                'media_type' => $videoContent->getMediaType(),
                'video_description' => $videoContent->getVideoDescription(),
                'video_metadata' => $videoContent->getVideoMetadata(),
                'video_provider' => $videoContent->getVideoProvider(),
                'video_title' => $videoContent->getVideoTitle(),
                'video_url' => $videoContent->getVideoUrl(),
            ]
        );
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertCustomAttribute($actualResponse)
    {
        $customAttribute = 'customAttributeValue';
        $this->assertEquals($customAttribute, $actualResponse['attribute_code_custom']);
    }

    /**
     * @param ProductInterface $product
     * @param $actualResponse
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function assertOptions($product, $actualResponse)
    {
        $productOptions = $product->getOptions();
        $this->assertNotEmpty($actualResponse['options'], "Precondition failed: 'options' must not be empty");
        foreach ($actualResponse['options'] as $optionsArray) {
            $option = null;
            /** @var \Magento\Catalog\Model\Product\Option $optionSelect */
            foreach ($productOptions as $optionSelect) {
                $match = false;
                if ($optionSelect->getTitle() == $optionsArray['title']) {
                    $option = $optionSelect;
                    if (!empty($option->getValues())) {
                        $values = $option->getValues();
                        /** @var \Magento\Catalog\Model\Product\Option\Value $value */
                        $value = current($values);
                        $findValueKeyName = $option->getType() . '_option';
                        if ($value->getTitle() === $optionsArray[$findValueKeyName][0]['title']) {
                            $match = true;
                        }
                    } else {
                        $match = true;
                    }
                    if ($match) {
                        break;
                    }
                }
            }
            $assertionMap = [
                ['response_field' => 'sort_order', 'expected_value' => $option->getSortOrder()],
                ['response_field' => 'title', 'expected_value' => $option->getTitle()],
                ['response_field' => 'required', 'expected_value' => $option->getIsRequire()],
                ['response_field' => 'option_id', 'expected_value' => $option->getOptionId()]
            ];

            if (!empty($option->getValues())) {
                $valueKeyName = $option->getType() . '_option';
                $value = current($optionsArray[$valueKeyName]);
                /** @var \Magento\Catalog\Model\Product\Option\Value $productValue */
                $productValue = current($option->getValues());
                $assertionMapValues = [
                    ['response_field' => 'title', 'expected_value' => $productValue->getTitle()],
                    ['response_field' => 'sort_order', 'expected_value' => $productValue->getSortOrder()],
                    ['response_field' => 'price', 'expected_value' => $productValue->getPrice()],
                    ['response_field' => 'price_type', 'expected_value' => strtoupper($productValue->getPriceType())],
                    ['response_field' => 'sku', 'expected_value' => $productValue->getSku()],
                    ['response_field' => 'option_type_id', 'expected_value' => $productValue->getOptionTypeId()]
                ];
                $this->assertResponseFields($value, $assertionMapValues);
            } else {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $assertionMap = array_merge(
                    $assertionMap,
                    [
                        ['response_field' => 'product_sku', 'expected_value' => $option->getProductSku()],
                    ]
                );

                if ($option->getType() === 'file') {
                    $valueKeyName = 'file_option';
                    $valueAssertionMap = [
                        ['response_field' => 'file_extension', 'expected_value' => $option->getFileExtension()],
                        ['response_field' => 'image_size_x', 'expected_value' => $option->getImageSizeX()],
                        ['response_field' => 'image_size_y', 'expected_value' => $option->getImageSizeY()]
                    ];
                } elseif ($option->getType() === 'area') {
                    $valueKeyName = 'area_option';
                    $valueAssertionMap = [
                        ['response_field' => 'max_characters', 'expected_value' => $option->getMaxCharacters()],
                    ];
                } elseif ($option->getType() === 'field') {
                    $valueKeyName = 'field_option';
                    $valueAssertionMap = [
                        ['response_field' => 'max_characters', 'expected_value' => $option->getMaxCharacters()]
                    ];
                } else {
                    $valueKeyName = 'date_option';
                    $valueAssertionMap = [];
                }
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $valueAssertionMap = array_merge(
                    $valueAssertionMap,
                    [
                        ['response_field' => 'price', 'expected_value' => $option->getPrice()],
                        ['response_field' => 'price_type', 'expected_value' => strtoupper($option->getPriceType())],
                        ['response_field' => 'sku', 'expected_value' => $option->getSku()]
                    ]
                );

                $this->assertResponseFields($optionsArray[$valueKeyName], $valueAssertionMap);
            }
            $this->assertResponseFields($optionsArray, $assertionMap);
        }
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertBaseFields($product, $actualResponse)
    {

        $assertionMap = [
            ['response_field' => 'attribute_set_id', 'expected_value' => $product->getAttributeSetId()],
            ['response_field' => 'created_at', 'expected_value' => $product->getCreatedAt()],
            ['response_field' => 'id', 'expected_value' => $product->getId()],
            ['response_field' => 'name', 'expected_value' => $product->getName()],
            ['response_field' => 'price', 'expected_value' =>
                [
                    'minimalPrice' => [
                        'amount' => [
                            'value' => $product->getSpecialPrice(),
                            'currency' => 'USD'
                        ],
                        'adjustments' => []
                    ],
                    'regularPrice' => [
                        'amount' => [
                            'value' => $product->getPrice(),
                            'currency' => 'USD'
                        ],
                        'adjustments' => []
                    ],
                    'maximalPrice' => [
                        'amount' => [
                            'value' => $product->getSpecialPrice(),
                            'currency' => 'USD'
                        ],
                        'adjustments' => []
                    ],
                ]
            ],
            ['response_field' => 'sku', 'expected_value' => $product->getSku()],
            ['response_field' => 'type_id', 'expected_value' => $product->getTypeId()],
            ['response_field' => 'updated_at', 'expected_value' => $product->getUpdatedAt()],
            ['response_field' => 'weight', 'expected_value' => $product->getWeight()],
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertWebsites($product, $actualResponse)
    {
        $assertionMap = [
            [
                'id' => current($product->getExtensionAttributes()->getWebsiteIds()),
                'name' => 'Main Website',
                'code' => 'base',
                'sort_order' => 0,
                'default_group_id' => '1',
                'is_default' => true,
            ]
        ];

        $this->assertEquals($actualResponse, $assertionMap);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertProductLinks($product, $actualResponse)
    {
        /** @var ProductLinkInterface $productLinks */
        $productLinks = $product->getProductLinks();
        $productLink = $productLinks[0];
        $assertionMap = [
            ['response_field' => 'link_type', 'expected_value' => $productLink->getLinkType()],
            ['response_field' => 'linked_product_sku', 'expected_value' => $productLink->getLinkedProductSku()],
            ['response_field' => 'linked_product_type', 'expected_value' => $productLink->getLinkedProductType()],
            ['response_field' => 'position', 'expected_value' => $productLink->getPosition()],
            ['response_field' => 'sku', 'expected_value' => $productLink->getSku()],
        ];
        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertEavAttributes($product, $actualResponse)
    {
        $eavAttributes = [
            'url_key',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'country_of_manufacture',
            'gift_message_available',
            'news_from_date',
            'options_container',
            'special_price',
            'special_from_date',
            'special_to_date',
        ];
        $assertionMap = [];
        foreach ($eavAttributes as $attributeCode) {
            $expectedAttribute = $product->getCustomAttribute($attributeCode);

            $assertionMap[] = [
                'response_field' => $this->eavAttributesToGraphQlSchemaFieldTranslator($attributeCode),
                'expected_value' => $expectedAttribute ? $expectedAttribute->getValue() : null
            ];
        }

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * @param string $eavAttributeCode
     * @return string
     */
    private function eavAttributesToGraphQlSchemaFieldTranslator(string $eavAttributeCode)
    {
        switch ($eavAttributeCode) {
            case 'news_from_date':
                $eavAttributeCode = 'new_from_date';
                break;
            case 'news_to_date':
                $eavAttributeCode = 'new_to_date';
                break;
        }
        return $eavAttributeCode;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testProductInAllAnchoredCategories()
    {
        $query = <<<QUERY
{
    products(filter: {sku: {in: ["12345"]}})
    {
        items
        {
            sku
            name
            categories {
            id
            name
            is_anchor
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['categories'], "Categories must not be empty");
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
        $categoryIds  = [3, 4, 5];

        $productItemsInResponse = $response['products']['items'];
        $this->assertCount(1, $productItemsInResponse);
        $this->assertCount(3, $productItemsInResponse[0]['categories']);
        $categoriesInResponse = array_map(null, $categoryIds, $productItemsInResponse[0]['categories']);
        foreach ($categoriesInResponse as $key => $categoryData) {
            $this->assertNotEmpty($categoryData);
            /** @var Category | Category $category */
            $category = $categoryRepository->get($categoriesInResponse[$key][0]);
            $this->assertResponseFields(
                $categoriesInResponse[$key][1],
                [
                    'name' => $category->getName(),
                    'id' => $category->getId(),
                    'is_anchor' => $category->getIsAnchor()
                ]
            );
        }
    }

    /**
     * Set one of the categories directly assigned to the product as non -anchored.
     * Verify that the non-anchored category still shows in the response
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testProductWithNonAnchoredParentCategory()
    {
        $query = <<<QUERY
{
    products(filter: {sku: {in: ["12345"]}})
    {
        items
        {
            sku
            name
            categories {
            id
            name
            is_anchor
            }
        }
    }
}
QUERY;
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
        /** @var Category $nonAnchorCategory */
        $nonAnchorCategory = $categoryRepository->get(4);
        $nonAnchorCategory->setIsAnchor(false);
        $categoryRepository->save($nonAnchorCategory);
        $categoryIds  = [3, 4, 5];

        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['categories'], "Categories must not be empty");

        $productItemsInResponse = $response['products']['items'];
        $this->assertCount(1, $productItemsInResponse);
        $this->assertCount(3, $productItemsInResponse[0]['categories']);
        $categoriesInResponse = array_map(null, $categoryIds, $productItemsInResponse[0]['categories']);
        foreach ($categoriesInResponse as $key => $categoryData) {
            $this->assertNotEmpty($categoryData);
            /** @var Category | Category $category */
            $category = $categoryRepository->get($categoriesInResponse[$key][0]);
            $this->assertResponseFields(
                $categoriesInResponse[$key][1],
                [
                    'name' => $category->getName(),
                    'id' => $category->getId(),
                    'is_anchor' => $category->getIsAnchor()
                ]
            );
        }
    }
    /**
     * Set as non-anchored, one of the categories not directly assigned to the product
     * Verify that the category doesn't show in the response
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testProductInNonAnchoredSubCategories()
    {
        $this->markTestSkipped('MC-30965: Product contains invalid categories');

        $query = <<<QUERY
{
    products(filter: 
             {
             sku: {in:["12345"]}
             }
          )
    {
        items
        {
            sku
            name
            categories {
            id
            name
            is_anchor
            }
        }
    }
}
QUERY;
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
        /** @var Category $nonAnchorCategory */
        $nonAnchorCategory = $categoryRepository->get(3);
        //Set the parent category as non-anchored
        $nonAnchorCategory->setIsAnchor(false);
        $categoryRepository->save($nonAnchorCategory);
        $categoryIds  = [4, 5];

        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'][0]['categories'], "Categories must not be empty");

        $productItemsInResponse = $response['products']['items'];
        $this->assertCount(1, $productItemsInResponse);
        $this->assertCount(2, $productItemsInResponse[0]['categories']);
        $categoriesInResponse = array_map(null, $categoryIds, $productItemsInResponse[0]['categories']);
        foreach ($categoriesInResponse as $key => $categoryData) {
            $this->assertNotEmpty($categoryData);
            /** @var Category | Category $category */
            $category = $categoryRepository->get($categoriesInResponse[$key][0]);
            $this->assertResponseFields(
                $categoriesInResponse[$key][1],
                [
                    'name' => $category->getName(),
                    'id' => $category->getId(),
                    'is_anchor' => $category->getIsAnchor()
                ]
            );
        }
    }
}
