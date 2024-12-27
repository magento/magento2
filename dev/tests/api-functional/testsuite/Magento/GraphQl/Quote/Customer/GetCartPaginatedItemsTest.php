<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetCartPaginatedItemsTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthenticationHeader;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getCustomerAuthenticationHeader = $this->objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @dataProvider paginatedDataProvider
     * @throws \Exception
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'p1'], as: 'product1'),
        DataFixture(ProductFixture::class, ['sku' => 'p2'], as: 'product2'),
        DataFixture(ProductFixture::class, ['sku' => 'p3'], as: 'product3'),
        DataFixture(ProductFixture::class, ['sku' => 'p4'], as: 'product4'),
        DataFixture(ProductFixture::class, ['sku' => 'p5'], as: 'product5'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product1.id$', 'qty' => 1]),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product2.id$', 'qty' => 1]),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product3.id$', 'qty' => 1]),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product4.id$', 'qty' => 1]),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product5.id$', 'qty' => 1]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testGetCartPaginatedItems(array $expectedSkus, int $pageSize, int $currentPage, int $totalCount)
    {
        $customer = $this->fixtures->get('customer');
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId, $pageSize, $currentPage);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($customer->getEmail(), 'password')
        );

        $this->assertArrayNotHasKey('errors', $response);
        $this->assertEquals($totalCount, $response['cart']['itemsV2']['total_count']);
        $this->assertEquals($pageSize, $response['cart']['itemsV2']['page_info']['page_size']);
        $this->assertEquals($currentPage, $response['cart']['itemsV2']['page_info']['current_page']);
        $actualSkus = [];
        foreach ($response['cart']['itemsV2']['items'] as $item) {
            $actualSkus[] = $item['product']['sku'];
        }
        $this->assertEquals($expectedSkus, $actualSkus);
    }

    /**
     * @param string $maskedQuoteId
     * @param int $pageSize
     * @param int $currentPage
     * @return string
     */
    private function getQuery(string $maskedQuoteId, int $pageSize, int $currentPage): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedQuoteId}") {
    email
    itemsV2(pageSize: {$pageSize} currentPage: {$currentPage}) {
      total_count
      page_info {
        page_size
        current_page
        total_pages
      }
      items {
        id
        product {
          name
          sku
        }
        quantity
     }
   }
  }
}
QUERY;
    }

    /**
     * @return array
     */
    public function paginatedDataProvider(): array
    {
        return [
            [
                ['p1', 'p2', 'p3'],
                3,
                1,
                5
            ],
            [
                ['p4', 'p5'],
                3,
                2,
                5
            ],
        ];
    }
}
