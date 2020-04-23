<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Remove configurable product from cart testcases
 */
class RemoveConfigurableProductFromCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    public function testRemoveConfigurableProductFromCart()
    {
        $configurableOptionSku = 'simple_10';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_cart_with_configurable');
        $quoteItemId = $this->getQuoteItemIdBySku($configurableOptionSku);
        $query = $this->getQuery($maskedQuoteId, $quoteItemId);
        $response = $this->graphQlMutation($query);

        $this->assertArrayHasKey('cart', $response['removeItemFromCart']);
        $this->assertArrayHasKey('items', $response['removeItemFromCart']['cart']);
        $this->assertEquals(0, count($response['removeItemFromCart']['cart']['items']));
    }

    /**
     * @param string $maskedQuoteId
     * @param int $itemId
     * @return string
     */
    private function getQuery(string $maskedQuoteId, int $itemId): string
    {
        return <<<QUERY
mutation {
  removeItemFromCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cart_item_id: {$itemId}
    }
  ) {
    cart {
      items {
        quantity
      }
    }
  }
}
QUERY;
    }

    /**
     * Returns quote item ID by product's SKU
     *
     * @param string $sku
     * @return int
     */
    private function getQuoteItemIdBySku(string $sku): int
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_cart_with_configurable', 'reserved_order_id');
        /** @var Item $quoteItem */
        $quoteItemsCollection = $quote->getItemsCollection();
        foreach ($quoteItemsCollection->getItems() as $item) {
            if ($item->getSku() == $sku) {
                return (int)$item->getId();
            }
        }
    }
}
