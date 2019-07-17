<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\DataObject;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;

/**
 * Test loading of category tree
 */
class CategoryTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->get(CategoryRepository::class);
    }

    /**
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
        $response = $this->graphQlQuery($query);
        $responseDataObject = new DataObject($response);
        //Some sort of smoke testing
        self::assertEquals(
            'Its a description of Test Category 1.2',
            $responseDataObject->getData('category/children/0/children/1/description')
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
            7,
            $responseDataObject->getData('category/children')
        );
        self::assertCount(
            2,
            $responseDataObject->getData('category/children/0/children')
        );
        self::assertEquals(
            13,
            $responseDataObject->getData('category/children/0/children/1/id')
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testCategoriesTreeWithDisabledCategory()
    {
        $category = $this->categoryRepository->get(3);
        $category->setIsActive(false);
        $this->categoryRepository->save($category);

        $rootCategoryId = 2;
        $query = <<<QUERY
{
  category(id: {$rootCategoryId}) {
      id
      name
      level
      description
      children {
        id
        name
        productImagePreview: products(pageSize: 1) {
            items {
                id
                } 
            }
      }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('category', $response);
        $this->assertArrayHasKey('children', $response['category']);
        $this->assertSame(6, count($response['category']['children']));
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetCategoryById()
    {
        $categoryId = 13;
        $query = <<<QUERY
{
  category(id: {$categoryId}) {
      id
      name
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertEquals('Category 1.2', $response['category']['name']);
        self::assertEquals(13, $response['category']['id']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @expectedException \Exception
     * @expectedExceptionMessage Category doesn't exist
     */
    public function testGetDisabledCategory()
    {
        $categoryId = 8;
        $query = <<<QUERY
{
  category(id: {$categoryId}) {
      id
      name
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    public function testNonExistentCategoryWithProductCount()
    {
        $query = <<<QUERY
{
  category(id: 99) {
      product_count
    }
}
QUERY;

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Category doesn\'t exist');
        $this->graphQlQuery($query);
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
        description {
            html
        }
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
        short_description {
            html
        }
        sku
        small_image { url, label }
        thumbnail { url, label }
        special_from_date
        special_price
        special_to_date
        swatch_image
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
