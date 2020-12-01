<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test loading of category tree
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
        $this->objectManager = Bootstrap::getObjectManager();
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRootCategoryTree()
    {
        $query = <<<QUERY
{
  category {
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
        $this->assertCount(6, $response['category']['children']);
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
     */
    public function testGetDisabledCategory()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Category doesn\'t exist');

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

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetCategoryIdZero()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Category doesn\'t exist');

        $categoryId = 0;
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
    products(sort: {name: DESC}) {
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
                        ['sku' => 'simple-4'],
                        ['sku' => 'simple']
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
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->create(CategoryCollection::class);
        $categoryCollection->addFieldToFilter('name', 'Category 1.1.1');
        /** @var CategoryInterface $category */
        $category = $categoryCollection->getFirstItem();
        $categoryId = $category->getId();
        $this->assertNotEmpty($categoryId, "Preconditions failed: category is not available.");
        $query = <<<QUERY
{
  category(id: {$categoryId}) {
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
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedResponse = [
            'category' => [
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
            // update image to account for different stored image format
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
categoryList(filters: {ids: {in: ["$categoryId"]}}) {
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
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertNotEmpty($response['categoryList']);
        $categoryList = $response['categoryList'];
        $storeBaseUrl = $this->objectManager->get(StoreManagerInterface::class)->getStore()->getBaseUrl('media');
        $expectedImageUrl = rtrim($storeBaseUrl, '/') . '/' . ltrim($categoryModel->getImage(), '/');
        $expectedImageUrl = str_replace('index.php/', '', $expectedImageUrl);

        $this->assertEquals($categoryId, $categoryList[0]['id']);
        $this->assertEquals('Parent Image Category', $categoryList[0]['name']);
        $categoryList[0]['image'] = str_replace('index.php/', '', $categoryList[0]['image']);
        $this->assertEquals($expectedImageUrl, $categoryList[0]['image']);

        $childCategory = $categoryList[0]['children'][0];
        $this->assertEquals('Child Image Category', $childCategory['name']);
        $childCategory['image'] = str_replace('index.php/', '', $childCategory['image']);
        $this->assertEquals($expectedImageUrl, $childCategory['image']);
    }

    /**
     * Testing breadcrumbs that shouldn't include disabled parent categories
     *
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     */
    public function testBreadCrumbsWithDisabledParentCategory()
    {
        $parentCategoryId = 4;
        $childCategoryId = 5;
        $category = $this->categoryRepository->get($parentCategoryId);
        $category->setIsActive(false);
        $this->categoryRepository->save($category);

        $query = <<<QUERY
{
  category(id: {$childCategoryId}) {
    name
    breadcrumbs {
      category_id
      category_name
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedResponse = [
            'category' => [
                'name' => 'Category 1.1.1',
                'breadcrumbs' => [
                    [
                        'category_id' => 3,
                        'category_name' => "Category 1",
                    ]
                ]
            ]
        ];
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @return array
     */
    public function categoryImageDataProvider(): array
    {
        return [
            'default_filename_strategy' => [
                'image_prefix' => null
            ],
            'just_filename_strategy' => [
                'image_prefix' => ''
            ],
            'with_pub_media_strategy' => [
                'image_prefix' => '/pub/media/catalog/category/'
            ],
            'catalog_category_strategy' => [
                'image_prefix' => 'catalog/category/'
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
            ['response_field' => 'attribute_set_id', 'expected_value' => $product->getAttributeSetId()],
            ['response_field' => 'created_at', 'expected_value' => $product->getCreatedAt()],
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
