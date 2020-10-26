<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CompareList;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for Compare list feature
 */
class CompareListTest extends GraphQlAbstract
{
    private const PRODUCT_SKU_1 = 'simple1';
    private const PRODUCT_SKU_2 = 'simple2';

    /**
     * @var mixed
     */
    private $productRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }
    /**
     * Create compare list without product
     */
    public function testCreateCompareListWithoutProducts()
    {
        $response = $this->createCompareList();
        $uid = $response['createCompareList']['uid'];
        $this->uidAssertion($uid);
    }

    /**
     * Create compare list with products
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testCreateCompareListWithProducts()
    {
        $product1 = $this->productRepository->get(self::PRODUCT_SKU_1);
        $product2 = $this->productRepository->get(self::PRODUCT_SKU_2);

        $mutation =  <<<MUTATION
mutation{
  createCompareList(input:{products: [{$product1->getId()}, {$product2->getId()}]}){
	 uid
     items {
        product {
            sku 
        }
      }
  }
}
MUTATION;
        $response = $this->graphQlMutation($mutation);
        $uid = $response['createCompareList']['uid'];
        $this->uidAssertion($uid);
        $this->itemsAssertion($response['createCompareList']['items']);
    }

    /**
     * Add products to compare list
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testAddProductToCompareList()
    {
        $compareList = $this->createCompareList();
        $uid = $compareList['createCompareList']['uid'];
        $this->uidAssertion($uid);
        $response = $this->addProductsToCompareList($uid);
        $resultUid = $response['addProductsToCompareList']['uid'];
        $this->uidAssertion($resultUid);
        $this->itemsAssertion($response['addProductsToCompareList']['items']);
    }

    /**
     * Remove products from compare list
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testRemoveProductFromCompareList()
    {
        $compareList = $this->createCompareList();
        $uid = $compareList['createCompareList']['uid'];
        $this->uidAssertion($uid);
        $addProducts = $this->addProductsToCompareList($uid);
        $this->itemsAssertion($addProducts['addProductsToCompareList']['items']);
        $this->assertCount(2, $addProducts['addProductsToCompareList']['items']);
        $product = $this->productRepository->get(self::PRODUCT_SKU_1);
        $removeFromCompareList =  <<<MUTATION
mutation{
  removeProductsFromCompareList(input: {uid: "{$uid}", products: [{$product->getId()}]}) {
    uid
    items {
        product {
            sku 
        }
    }
  }
}
MUTATION;
        $response = $this->graphQlMutation($removeFromCompareList);
        $this->assertCount(1, $response['removeProductsFromCompareList']['items']);
    }

    /**
     * Get compare list query
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testGetCompareList()
    {
        $compareList = $this->createCompareList();
        $uid = $compareList['createCompareList']['uid'];
        $this->uidAssertion($uid);
        $addProducts = $this->addProductsToCompareList($uid);
        $this->itemsAssertion($addProducts['addProductsToCompareList']['items']);
        $query =  <<<QUERY
{
  compareList(uid: "{$uid}") {
    uid
    items {
        product {
            sku 
        }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->itemsAssertion($response['compareList']['items']);
    }

    /**
     * Remove compare list
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testDeleteCompareList()
    {
        $compareList = $this->createCompareList();
        $uid = $compareList['createCompareList']['uid'];
        $this->uidAssertion($uid);
        $addProducts = $this->addProductsToCompareList($uid);
        $this->itemsAssertion($addProducts['addProductsToCompareList']['items']);
        $deleteCompareList =  <<<MUTATION
mutation{
  deleteCompareList(uid:"{$uid}") {
    result
  }
}
MUTATION;
        $response = $this->graphQlMutation($deleteCompareList);
        $this->assertTrue($response['deleteCompareList']['result']);
        $response1 = $this->graphQlMutation($deleteCompareList);
        $this->assertFalse($response1['deleteCompareList']['result']);
    }

    /**
     * Create compare list
     *
     * @return array
     */
    private function createCompareList(): array
    {
        $mutation =  <<<MUTATION
mutation{
  createCompareList {
	 uid
  }
}
MUTATION;
        return $this->graphQlMutation($mutation);
    }

    /**
     * Add products to compare list
     *
     * @param $uid
     *
     * @return array
     */
    private function addProductsToCompareList($uid): array
    {
        $product1 = $this->productRepository->get(self::PRODUCT_SKU_1);
        $product2 = $this->productRepository->get(self::PRODUCT_SKU_2);
        $addProductsToCompareList =  <<<MUTATION
mutation{
    addProductsToCompareList(input: { uid: "{$uid}", products: [{$product1->getId()}, {$product2->getId()}]}) {
        uid
        items {
            product {
                sku 
            }
        }
    }
}
MUTATION;
        return $this->graphQlMutation($addProductsToCompareList);
    }

    /**
     * Assert UID
     *
     * @param string $uid
     */
    private function uidAssertion(string $uid)
    {
        $this->assertIsString($uid);
        $this->assertEquals(32, strlen($uid));
    }

    /**
     * Assert products
     *
     * @param array $items
     */
    private function itemsAssertion(array $items)
    {
        $this->assertArrayHasKey(0, $items);
        $this->assertArrayHasKey(1, $items);
        $this->assertEquals(self::PRODUCT_SKU_1, $items[0]['product']['sku']);
        $this->assertEquals(self::PRODUCT_SKU_2, $items[1]['product']['sku']);
    }
}
