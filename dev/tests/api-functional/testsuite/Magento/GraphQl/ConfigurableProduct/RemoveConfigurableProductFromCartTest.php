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
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @param string $itemArgName
     * @param string $reservedOrderId
     * @dataProvider removeConfigurableProductFromCartDataProvider
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    public function testRemoveConfigurableProductFromCart(string $itemArgName, string $reservedOrderId)
    {
        $configurableOptionSku = 'simple_10';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);
        $quoteItemId = $this->getQuoteItemIdBySku($configurableOptionSku);
        if ($itemArgName === 'cart_item_uid') {
            $quoteItemId = base64_encode($quoteItemId);
        }
        $query = $this->getQuery($itemArgName, $maskedQuoteId, $quoteItemId);
        $response = $this->graphQlMutation($query);

        $this->assertArrayHasKey('cart', $response['removeItemFromCart']);
        $this->assertArrayHasKey('items', $response['removeItemFromCart']['cart']);
        $this->assertCount(0, $response['removeItemFromCart']['cart']['items']);
    }

    /**
     * Data provider for testUpdateConfigurableCartItemQuantity
     *
     * @return array
     */
    public function removeConfigurableProductFromCartDataProvider(): array
    {
        return [
            ['cart_item_id', 'test_cart_with_configurable'],
            ['cart_item_uid', 'test_cart_with_configurable'],
        ];
    }

    /**
     * @param string $itemArgName
     * @param string $maskedQuoteId
     * @param string $itemId
     * @return string
     */
    private function getQuery(string $itemArgName, string $maskedQuoteId, string $itemId): string
    {
        if (is_numeric($itemId)) {
            $itemId = (int) $itemId;
        } else {
            $itemId = '"' . $itemId . '"';
        }
        return <<<QUERY
mutation {
  removeItemFromCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      {$itemArgName}: {$itemId}
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
     * @return string
     */
    private function getQuoteItemIdBySku(string $sku): string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_cart_with_configurable', 'reserved_order_id');
        /** @var Item $quoteItem */
        $quoteItemsCollection = $quote->getItemsCollection();
        foreach ($quoteItemsCollection->getItems() as $item) {
            if ($item->getSku() == $sku) {
                return $item->getId();
            }
        }
    }
}
