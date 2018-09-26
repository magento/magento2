<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\DataObject;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;

class CategoryTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCategoriesTree()
    {
        $rootCategoryId = 2;
        $query = <<<QUERY
{
  category(id: {$rootCategoryId}) {
      id
      level
      description
      path
      path_in_store
      product_count
      url_key
      url_path
      children {
        id
        description
        available_sort_by
        default_sort_by
        image
        level
        children {
          id
          filter_price_range
          description
          image
          meta_keywords
          level
          is_anchor
          children {
            level
            id
          }
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
        $responseDataObject = new DataObject($response);
        //Some sort of smoke testing
        self::assertEquals(
            'Ololo',
            $responseDataObject->getData('category/children/7/children/1/description')
        );
        self::assertEquals(
            'default-category',
            $responseDataObject->getData('category/url_key')
        );
        self::assertEquals(
            [],
            $responseDataObject->getData('category/children/0/available_sort_by')
        );
        self::assertEquals(
            'name',
            $responseDataObject->getData('category/children/0/default_sort_by')
        );
        self::assertCount(
            8,
            $responseDataObject->getData('category/children')
        );
        self::assertCount(
            2,
            $responseDataObject->getData('category/children/7/children')
        );
        self::assertEquals(
            5,
            $responseDataObject->getData('category/children/7/children/1/children/0/id')
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCategoryProducts()
    {
        $categoryId = 2;
        $query = <<<QUERY
{
  category(id: {$categoryId}) {
    products {
      total_count
      page_info {
        current_page
        page_size
      }
      items {
        attribute_set_id
        country_of_manufacture
        created_at
        description
        gift_message_available
        id
        categories {
          name
          url_path
          available_sort_by
          level
        }
        image
        image_label
        meta_description
        meta_keyword
        meta_title
        media_gallery_entries {
          disabled
          file
          id
          label
          media_type
          position
          types
          content {
            base64_encoded_data
            type
            name
          }
          video_content {
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
        product_links {
          link_type
          linked_product_sku
          linked_product_type
          position
          sku
        }
        short_description
        sku
        small_image {
            path
        }
        small_image_label
        special_from_date
        special_price
        special_to_date
        swatch_image
        thumbnail
        thumbnail_label
        tier_price
        tier_prices {
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
        websites {
          id
          name
          code
          sort_order
          default_group_id
          is_default
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response['category']);
        $this->assertArrayHasKey('total_count', $response['category']['products']);
        $this->assertGreaterThanOrEqual(1, $response['category']['products']['total_count']);
        $this->assertEquals(1, $response['category']['products']['page_info']['current_page']);
        $this->assertEquals(20, $response['category']['products']['page_info']['page_size']);

        $this->assertArrayHasKey('sku', $response['category']['products']['items'][0]);
        $firstProductSku = $response['category']['products']['items'][0]['sku'];

        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $firstProduct = $productRepository->get($firstProductSku, false, null, true);
        $this->assertBaseFields($firstProduct, $response['category']['products']['items'][0]);
        $this->assertAttributes($response['category']['products']['items'][0]);
        $this->assertWebsites($firstProduct, $response['category']['products']['items'][0]['websites']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testAnchorCategory()
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->create(CategoryCollection::class);
        $categoryCollection->addFieldToFilter('name', 'Category 1');
        /** @var CategoryInterface $category */
        $category = $categoryCollection->getFirstItem();
        $categoryId = $category->getId();

        $this->assertNotEmpty($categoryId, "Preconditions failed: category is not available.");

        $query = <<<QUERY
{
  category(id: {$categoryId}) {
    name
    products(sort: {sku: ASC}) {
      total_count
      items {
        sku
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $expectedResponse = [
            'category' => [
                'name' => 'Category 1',
                'products' => [
                    'total_count' => 3,
                    'items' => [
                        ['sku' => '12345'],
                        ['sku' => 'simple'],
                        ['sku' => 'simple-4']
                    ]
                ]
            ]
        ];
        $this->assertEquals($expectedResponse, $response);
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
            ['response_field' => 'name', 'expected_value' => $product->getName()],
            ['response_field' => 'price', 'expected_value' =>
                [
                    'minimalPrice' => [
                        'amount' => [
                            'value' => $product->getPrice(),
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
                            'value' => $product->getPrice(),
                            'currency' => 'USD'
                        ],
                        'adjustments' => []
                    ],
                ]
            ],
            ['response_field' => 'sku', 'expected_value' => $product->getSku()],
            ['response_field' => 'type_id', 'expected_value' => $product->getTypeId()],
            ['response_field' => 'updated_at', 'expected_value' => $product->getUpdatedAt()],
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
     * @param array $actualResponse
     */
    private function assertAttributes($actualResponse)
    {
        $eavAttributes = [
            'url_key',
            'description',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'short_description',
            'country_of_manufacture',
            'gift_message_available',
            'new_from_date',
            'new_to_date',
            'options_container',
            'special_price',
            'special_from_date',
            'special_to_date',
        ];

        foreach ($eavAttributes as $eavAttribute) {
            $this->assertArrayHasKey($eavAttribute, $actualResponse);
        }
    }
}
