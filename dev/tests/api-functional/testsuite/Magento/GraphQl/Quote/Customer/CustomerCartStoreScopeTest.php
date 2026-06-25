<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Store\Test\Fixture\Store;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Ensures customerCart respects Store header for store-scoped product attributes
 */
class CustomerCartStoreScopeTest extends GraphQlAbstract
{
    /** @var CustomerTokenServiceInterface */
    private $customerTokenService;

    /**
     * @var string Customer token for requests
     */
    private $customerToken;

    protected function setUp(): void
    {
        $this->customerTokenService = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Customer cart returns product name overridden per Store header
     */
    #[
        DataFixture(Store::class, as: 'store2'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(ProductFixture::class, ['name' => 'Def Name'], as: 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 1
            ]
        )
    ]
    public function testCustomerCartProductNameRespectsStoreHeader(): void
    {
        //Set product name per store
        $secondStore = DataFixtureStorageManager::getStorage()->get('store2');
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $product->setStoreId($secondStore->getId())->setName('Second Name');
        $productRepository->save($product);
        //get customer cart for different stores
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $query = <<<QUERY
{
  customerCart {
    itemsV2(pageSize: 10) {
      items { product { sku name } }
    }
  }
}
QUERY;

        // Default store
        $headersDefault = $this->getHeaderMap($customer->getEmail());
        $headersDefault['Store'] = 'default';
        $responseDefault = $this->graphQlQuery($query, [], '', $headersDefault);
        self::assertEquals('Def Name', $responseDefault['customerCart']['itemsV2']['items'][0]['product']['name']);

        // Second store
        $headersSecond = $this->getHeaderMap($customer->getEmail());
        $headersSecond['Store'] = $secondStore->getCode();
        $responseSecond = $this->graphQlQuery($query, [], '', $headersSecond);
        self::assertEquals('Second Name', $responseSecond['customerCart']['itemsV2']['items'][0]['product']['name']);
    }

    /**
     * Invalid Store header returns a standardized error
     */
    #[
        DataFixture(Customer::class, as: 'customer')
    ]
    public function testCustomerCartInvalidStoreHeader(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Requested store is not found');

        $query = <<<QUERY
{
  customerCart { id }
}
QUERY;
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $headers = $this->getHeaderMap($customer->getEmail());
        $headers['Store'] = 'not_existing_store';
        $this->graphQlQuery($query, [], '', $headers);
    }

    /**
     * Get headers with authorization token
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $token = $this->getCustomerToken($username, $password);
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Get and cache customer token
     *
     * @param string $username
     * @param string $password
     * @return string
     */
    private function getCustomerToken(
        string $username,
        string $password
    ) : string {
        if (!$this->customerToken) {
            $this->customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        }
        return $this->customerToken;
    }
}
