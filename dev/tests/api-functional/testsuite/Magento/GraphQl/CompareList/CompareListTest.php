<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CompareList;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for Compare list feature
 */
class CompareListTest extends GraphQlAbstract
{
    private const PRODUCT_SKU_1 = 'simple1';
    private const PRODUCT_SKU_2 = 'simple2';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
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
        $this->assertEquals(0, $compareList['createCompareList']['item_count'],'Incorrect count');
        $this->uidAssertion($uid);
        $response = $this->addProductsToCompareList($uid);
        $resultUid = $response['addProductsToCompareList']['uid'];
        $this->uidAssertion($resultUid);
        $this->itemsAssertion($response['addProductsToCompareList']['items']);
        $this->assertEquals(2, $response['addProductsToCompareList']['item_count'],'Incorrect count');
        $this->assertResponseFields(
            $response['addProductsToCompareList']['attributes'],
            [
                [
                    'code'=> 'sku',
                    'label'=> 'SKU'
                ],
                [
                    'code'=> 'description',
                    'label'=> 'Description'
                ],
                [
                    'code'=> 'short_description',
                    'label'=> 'Short Description'
                ]
            ]
        );
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
     * Assign compare list to customer
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testAssignCompareListToCustomer()
    {
        $compareList = $this->createCompareList();
        $uid = $compareList['createCompareList']['uid'];
        $this->uidAssertion($uid);
        $addProducts = $this->addProductsToCompareList($uid);
        $this->itemsAssertion($addProducts['addProductsToCompareList']['items']);
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $customerQuery = <<<QUERY
{
  customer {
    firstname
    lastname
    compare_list {
      uid
      items {
        product {
            sku
        }
      }
    }
  }
}
QUERY;
        $customerResponse = $this->graphQlQuery(
            $customerQuery,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
        $this->assertArrayHasKey('compare_list', $customerResponse['customer']);
        $this->assertNull($customerResponse['customer']['compare_list']);

        $assignCompareListToCustomer = <<<MUTATION
mutation {
  assignCompareListToCustomer(uid: "{$uid}"){
    result
    compare_list {
      uid
      items {
        uid
      }
    }
  }
}
MUTATION;
        $assignResponse = $this->graphQlMutation(
            $assignCompareListToCustomer,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
        $this->assertTrue($assignResponse['assignCompareListToCustomer']['result']);

        $customerAssignedResponse = $this->graphQlQuery(
            $customerQuery,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        $this->assertArrayHasKey('compare_list', $customerAssignedResponse['customer']);
        $this->uidAssertion($customerAssignedResponse['customer']['compare_list']['uid']);
        $this->itemsAssertion($customerAssignedResponse['customer']['compare_list']['items']);
    }

    /**
     * Assign compare list of one customer to another customer
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testCompareListsNotAccessibleBetweenCustomers()
    {
        $uidCustomer1 = $this->createCompareListForCustomer('customer@example.com', 'password');
        $uidcustomer2 = $this->createCompareListForCustomer('customer_two@example.com', 'password');
        $assignCompareListToCustomer = <<<MUTATION
mutation {
  assignCompareListToCustomer(uid: "{$uidCustomer1}"){
    result
    compare_list {
      uid
      items {
        uid
      }
    }
  }
}
MUTATION;

        $expectedExceptionsMessage = 'GraphQL response contains errors: This customer is not authorized to access this list';
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage($expectedExceptionsMessage);
        //customer2 not allowed to assign compareList belonging to customer1
        $this->graphQlMutation(
            $assignCompareListToCustomer,
            [],
            '',
            $this->getCustomerAuthHeaders('customer_two@example.com', 'password')
        );

        $deleteCompareList =  <<<MUTATION
mutation{
  deleteCompareList(uid:"{$uidcustomer2}") {
    result
  }
}
MUTATION;
        $expectedExceptionsMessage = 'GraphQL response contains errors: This customer is not authorized to access this list';
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage($expectedExceptionsMessage);
        //customer1 not allowed to delete compareList belonging to customer2
        $this->graphQlMutation(
            $assignCompareListToCustomer,
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );

    }

    /**
     * Get customer Header
     *
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
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
	 item_count
	 attributes{code label}
  }
}
MUTATION;
        return $this->graphQlMutation($mutation);
    }

    private function createCompareListForCustomer(string $username, string $password): string
    {
        $compareListCustomer =  <<<MUTATION
mutation{
  createCompareList {
	 uid
  }
}
MUTATION;
        $response = $this->graphQlMutation(
            $compareListCustomer,
            [],
            '',
            $this->getCustomerAuthHeaders($username, $password)
        );

        return $response['createCompareList']['uid'];
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
        item_count
        attributes{code label}
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
