<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Framework\DataObject;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Api\ProductRepositoryInterface;

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
            children {
              id
            }
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
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCategoryProducts()
    {
        $categoryId = 4;
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
        small_image
        small_image_label
        special_from_date
        special_price
        special_to_date
        swatch_image
        tax_class_id
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
        $this->assertEquals(2, $response['category']['products']['total_count']);
        $this->assertEquals(1, $response['category']['products']['page_info']['current_page']);
        $this->assertEquals(20, $response['category']['products']['page_info']['page_size']);
    }
}
