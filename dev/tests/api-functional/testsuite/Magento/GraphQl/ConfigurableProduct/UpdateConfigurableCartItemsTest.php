<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * checks that qty of configurable product is updated in cart
 */
class UpdateConfigurableCartItemsTest extends GraphQlAbstract
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/quote_with_configurable_product.php
     */
    public function testUpdateConfigurableCartItemQuantity()
    {
        $reservedOrderId = 'test_cart_with_configurable';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $productSku = 'simple_10';
        $newQuantity = 123;
        $quoteItem = $this->getQuoteItemBySku($productSku, $reservedOrderId);

        $query = $this->getQuery($maskedQuoteId, (int)$quoteItem->getId(), $newQuantity);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('updateCartItems', $response);
        self::assertArrayHasKey('quantity', $response['updateCartItems']['cart']['items']['0']);
        self::assertEquals($newQuantity, $response['updateCartItems']['cart']['items']['0']['quantity']);
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteIdMaskFactory = Bootstrap::getObjectManager()->get(QuoteIdMaskFactory::class);
    }

    /**
     * @param string $maskedQuoteId
     * @param int $quoteItemId
     * @param int $newQuantity
     * @return string
     */
    private function getQuery(string $maskedQuoteId, int $quoteItemId, int $newQuantity): string
    {
        return <<<QUERY
mutation {
  updateCartItems(input: {
    cart_id:"$maskedQuoteId"
    cart_items: [
      {
        cart_item_id: $quoteItemId
        quantity: $newQuantity
      }
    ]
  }) {
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
     * Returns quote item by product SKU
     *
     * @param string $sku
     * @return Item|bool
     * @throws NoSuchEntityException
     */
    private function getQuoteItemBySku(string $sku, string $reservedOrderId)
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $item = false;
        foreach ($quote->getAllItems() as $quoteItem) {
            if ($quoteItem->getSku() == $sku && $quoteItem->getProductType() == Configurable::TYPE_CODE &&
                !$quoteItem->getParentItemId()) {
                $item = $quoteItem;
                break;
            }
        }

        return $item;
    }
}
