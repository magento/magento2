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
     * @magentoApiDataFixture Magento/Catalog/_files/categories_no_products.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param int $categoryId
     * @param array $expectedResponse
     *
     * @dataProvider categorySubtreeTestDataProvider
     */
    public function testCategoriesSubtree($categoryId, $expectedResponse)
    {
        $query = <<<QUERY
{
  category(
    id: {$categoryId}) {
    id
    level
    name
    path
    product_count
    children {
        id
        name
        level
        path
        children {
            id
            name
            level
            description
            path
        }
    }
  }
}
QUERY;
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $customerToken = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals($expectedResponse, $response);
    }

    public function categorySubtreeTestDataProvider()
    {
        return [
            [
                'category_id' => 6,
                'expected_subtree' => [
                    'category' => [
                        'id' => 6,
                        'level' => 2,
                        'name' => 'Category 2',
                        'path' => '1/2/6',
                        'product_count' => 0,
                        'children' => []
                    ],
                ]
            ]
        ];
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
        var_dump($response['category']['children']);
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
}
