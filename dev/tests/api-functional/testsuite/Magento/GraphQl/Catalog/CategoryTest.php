<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Framework\DataObject;
use Magento\TestFramework\TestCase\GraphQlAbstract;

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
  categories(filter: {root_category_id: {$rootCategoryId}}) {
    category_tree {
      id
      level
      is_active
      description
      all_children
      path
      path_in_store
      product_count
      url_key
      url_path
      children {
        id
        is_active
        description
        available_sort_by
        default_sort_by
        image
        level
        children {
          id
          is_active
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
            $responseDataObject->getData('categories/category_tree/children/7/children/1/description')
        );
        self::assertEquals(
            'default-category',
            $responseDataObject->getData('categories/category_tree/url_key')
        );
        self::assertEquals(
            [],
            $responseDataObject->getData('categories/category_tree/children/0/available_sort_by')
        );
        self::assertEquals(
            'name',
            $responseDataObject->getData('categories/category_tree/children/0/default_sort_by')
        );
        self::assertCount(
            8,
            $responseDataObject->getData('categories/category_tree/children')
        );
        self::assertCount(
            2,
            $responseDataObject->getData('categories/category_tree/children/7/children')
        );
        self::assertEquals(
            5,
            $responseDataObject->getData('categories/category_tree/children/7/children/1/children/0/id')
        );
    }
}
