<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\CategoriesQuery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test category tree data is returned correctly from "categories" query
 */
class CategoryTreeTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->get(CategoryRepository::class);
        $this->store = $this->objectManager->get(Store::class);
        $this->metadataPool = $this->objectManager->get(MetadataPool::class);
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
  categories(filters: {ids: {eq: "{$rootCategoryId}"}}) {
    items{
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
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('categories', $response);
        $baseCategory = $response['categories']['items'][0];
        $this->assertEquals(
            'Its a description of Test Category 1.2',
            $baseCategory['children'][0]['children'][1]['description']
        );
        $this->assertEquals('default-category', $baseCategory['url_key']);
        $this->assertEquals(null, $baseCategory['children'][0]['available_sort_by']);
        $this->assertEquals('name', $baseCategory['children'][0]['default_sort_by']);
        $this->assertCount(7, $baseCategory['children']);
        $this->assertCount(2, $baseCategory['children'][0]['children']);
        $this->assertEquals(13, $baseCategory['children'][0]['children'][1]['id']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRootCategoryTree()
    {
        $query = <<<QUERY
{
  categories {
    items{
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
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('categories', $response);
        $baseCategory = $response['categories']['items'][0];
        $this->assertEquals(
            'Its a description of Test Category 1.2',
            $baseCategory['children'][0]['children'][1]['description']
        );
        $this->assertEquals('default-category', $baseCategory['url_key']);
        $this->assertEquals(null, $baseCategory['children'][0]['available_sort_by']);
        $this->assertEquals('name', $baseCategory['children'][0]['default_sort_by']);
        $this->assertCount(7, $baseCategory['children']);
        $this->assertCount(2, $baseCategory['children'][0]['children']);
        $this->assertEquals(13, $baseCategory['children'][0]['children'][1]['id']);
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
  categories(filters: {ids: {eq: "{$rootCategoryId}"}}) {
    items{
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
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('categories', $response);
        $this->assertArrayHasKey('children', $response['categories']['items'][0]);
        $this->assertCount(6, $response['categories']['items'][0]['children']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetCategoryById()
    {
        $categoryId = 13;
        $query = <<<QUERY
{
  categories(filters: {ids: {eq: "{$categoryId}"}}) {
    items{
      id
      name
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals('Category 1.2', $response['categories']['items'][0]['name']);
        $this->assertEquals(13, $response['categories']['items'][0]['id']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetDisabledCategory()
    {
        $categoryId = 8;
        $query = <<<QUERY
{
  categories(filters: {ids: {eq: "{$categoryId}"}}) {
    items{
      id
      name
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('items', $response['categories']);
        $this->assertEquals([], $response['categories']['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetCategoryIdZero()
    {
        $query = <<<QUERY
{
  categories(filters: {ids: {eq: "0"}}) {
    items{
      id
      name
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('items', $response['categories']);
        $this->assertEquals([], $response['categories']['items']);
    }

    public function testNonExistentCategoryWithProductCount()
    {
        $query = <<<QUERY
{
  categories(filters: {ids: {eq: "99"}}) {
    total_count
    items{
      product_count
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('items', $response['categories']);
        $this->assertEquals([], $response['categories']['items']);
        $this->assertEquals(0, $response['categories']['total_count']);
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
  categories(filters: {ids: {eq: "{$categoryId}"}}) {
  items{
    products {
      total_count
      page_info {
        current_page
        page_size
      }
      items {
        country_of_manufacture
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
        url_key
        url_path
      }
    }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response['categories']['items'][0]);
        $baseCategory = $response['categories']['items'][0];
        $this->assertArrayHasKey('total_count', $baseCategory['products']);
        $this->assertGreaterThanOrEqual(1, $baseCategory['products']['total_count']);
        $this->assertEquals(1, $baseCategory['products']['page_info']['current_page']);
        $this->assertEquals(20, $baseCategory['products']['page_info']['page_size']);
        $this->assertArrayHasKey('sku', $baseCategory['products']['items'][0]);
        $firstProduct = $baseCategory['products']['items'][0];
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $firstProductModel = $productRepository->get($firstProduct['sku'], false, null, true);
        $this->assertBaseFields($firstProductModel, $firstProduct);
        $this->assertAttributes($firstProduct);
        $this->assertEquals('Category 1', $firstProduct['categories'][0]['name']);
        $this->assertEquals('category-1/category-1-1', $firstProduct['categories'][1]['url_path']);
        $this->assertCount(3, $firstProduct['categories']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testAnchorCategory()
    {
        $query = <<<QUERY
{
  categories(filters: {url_key: {eq: "category-1"}}) {
    items {
      name
      products(sort: {name: DESC}) {
        total_count
        items {
          sku
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedResponse = [
            'categories' => [
                'items' => [
                    0 => [
                        'name' => 'Category 1',
                        'products' => [
                            'total_count' => 3,
                            'items' => [
                                ['sku' => '12345'],
                                ['sku' => 'simple-4'],
                                ['sku' => 'simple']
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testBreadCrumbs()
    {
        $query = <<<QUERY
{
  categories(filters: {url_key: {eq: "category-1-1-1"}}) {
    items{
      name
      breadcrumbs {
        category_id
        category_name
        category_level
        category_url_key
        category_url_path
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedResponse = [
            'categories' => [
                'items' => [
                    0 => [
                        'name' => 'Category 1.1.1',
                        'breadcrumbs' => [
                            [
                                'category_id' => 3,
                                'category_name' => "Category 1",
                                'category_level' => 2,
                                'category_url_key' => "category-1",
                                'category_url_path' => "category-1"
                            ],
                            [
                                'category_id' => 4,
                                'category_name' => "Category 1.1",
                                'category_level' => 3,
                                'category_url_key' => "category-1-1",
                                'category_url_path' => "category-1/category-1-1"
                            ],
                        ]
                    ]
                ]
            ]
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Test category image is returned as full url (not relative path)
     *
     * @param string $imagePrefix
     * @magentoApiDataFixture Magento/Catalog/_files/catalog_category_with_image.php
     * @dataProvider categoryImageDataProvider
     */
    public function testCategoryImage(?string $imagePrefix)
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->get(CategoryCollection::class);
        $categoryModel = $categoryCollection
            ->addAttributeToSelect('image')
            ->addAttributeToFilter('name', ['eq' => 'Parent Image Category'])
            ->getFirstItem();
        $categoryId = $categoryModel->getId();
        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = Bootstrap::getObjectManager()->create(ResourceConnection::class);
        $connection = $resourceConnection->getConnection();

        if ($imagePrefix !== null) {
            // update image to account for different stored image formats
            $productLinkField = $this->metadataPool
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();

            $defaultStoreId = $this->store->getId();

            $imageAttributeValue = $imagePrefix . basename($categoryModel->getImage());

            if (!empty($imageAttributeValue)) {
                $sqlQuery = sprintf(
                    'UPDATE %s SET `value` = "%s" ' .
                    'WHERE `%s` = %d ' .
                    'AND `store_id`= %d ' .
                    'AND `attribute_id` = ' .
                    '(SELECT `ea`.`attribute_id` FROM %s ea WHERE `ea`.`attribute_code` = "image" LIMIT 1)',
                    $resourceConnection->getTableName('catalog_category_entity_varchar'),
                    $imageAttributeValue,
                    $productLinkField,
                    $categoryModel->getData($productLinkField),
                    $defaultStoreId,
                    $resourceConnection->getTableName('eav_attribute')
                );
                $connection->query($sqlQuery);
            }
        }

        $query = <<<QUERY
{
categories(filters: {ids: {in: ["$categoryId"]}}) {
  items {
    id
    name
    url_key
    image
    children {
      id
      name
      url_key
      image
    }
  }
}
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertNotEmpty($response['categories']);
        $categories = $response['categories']['items'];
        $storeBaseUrl = $this->objectManager->get(StoreManagerInterface::class)->getStore()->getBaseUrl('media');
        $expectedImageUrl = rtrim($storeBaseUrl, '/') . '/' . ltrim($categoryModel->getImage(), '/');
        $expectedImageUrl = str_replace('index.php/', '', $expectedImageUrl);

        $this->assertEquals($categoryId, $categories[0]['id']);
        $this->assertEquals('Parent Image Category', $categories[0]['name']);
        $categories[0]['image'] = str_replace('index.php/', '', $categories[0]['image']);
        $this->assertEquals($expectedImageUrl, $categories[0]['image']);

        $childCategory = $categories[0]['children'][0];
        $this->assertEquals('Child Image Category', $childCategory['name']);
        $childCategory['image'] = str_replace('index.php/', '', $childCategory['image']);
        $this->assertEquals($expectedImageUrl, $childCategory['image']);
    }

    /**
     * Test categories query when category image is not found or missing.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/catalog_category_with_missing_image.php
     */
    public function testCategoriesQueryWhenCategoryImageIsMissing(): void
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->get(CategoryCollection::class);
        $categoryModel = $categoryCollection
            ->addAttributeToSelect('image')
            ->addAttributeToFilter('name', ['eq' => 'Parent Image Category'])
            ->getFirstItem();
        $categoryId = $categoryModel->getId();
        $query = <<<QUERY
{
    categories(filters: {ids: {in: ["$categoryId"]}}) {
        items {
            id
            name
            url_key
            image
            children {
                id
                name
                url_key
                image
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertNotEmpty($response['categories']);
        $categories = current($response['categories']['items']);
        $this->assertEquals($categoryId, $categories['id']);
        $this->assertEquals('Parent Image Category', $categories['name']);
        $this->assertStringEndsWith('Magento_Catalog/images/category/placeholder/image.jpg', $categories['image']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetCategoryWithIdAndUid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('`ids` and `category_uid` can\'t be used at the same time');

        $categoryId = 8;
        $categoryUid = base64_encode((string) 8);
        $query = <<<QUERY
{
categories(filters: {ids: {in: ["$categoryId"]}, category_uid: {in: ["$categoryUid"]}}) {
  items {
    id
    name
    url_key
    image
    children {
      id
      name
      url_key
      image
    }
  }
}
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @return array
     */
    public static function categoryImageDataProvider(): array
    {
        return [
            'default_filename_strategy' => [
                'imagePrefix' => null
            ],
            'just_filename_strategy' => [
                'imagePrefix' => ''
            ],
            'with_pub_media_strategy' => [
                'imagePrefix' => '/media/catalog/category/'
            ],
            'catalog_category_strategy' => [
                'imagePrefix' => 'catalog/category/'
            ],
        ];
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertBaseFields($product, $actualResponse)
    {
        $assertionMap = [
            ['response_field' => 'name', 'expected_value' => $product->getName()],
            ['response_field' => 'price', 'expected_value' => [
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
        ];
        $this->assertResponseFields($actualResponse, $assertionMap);
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
            'special_to_date',
        ];
        foreach ($eavAttributes as $eavAttribute) {
            $this->assertArrayHasKey($eavAttribute, $actualResponse);
        }
    }
}
