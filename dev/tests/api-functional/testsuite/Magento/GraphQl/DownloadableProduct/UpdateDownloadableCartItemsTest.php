<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\DownloadableProduct;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Quote\GetQuoteItemIdByReservedQuoteIdAndSku;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test cases for adding downloadable product to cart.
 */
class UpdateDownloadableCartItemsTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GetQuoteItemIdByReservedQuoteIdAndSku
     */
    private $getQuoteItemIdByReservedQuoteIdAndSku;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->quoteFactory = $this->objectManager->get(QuoteFactory::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->quoteResource = $this->objectManager->get(QuoteResource::class);
        $this->getQuoteItemIdByReservedQuoteIdAndSku = $this->objectManager->get(
            GetQuoteItemIdByReservedQuoteIdAndSku::class
        );
    }

    /**
     * Update a downloadable product into shopping cart when "Links can be purchased separately" is enabled
     *
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote_with_downloadable_product.php
     */
    public function testUpdateDownloadableCartItemQuantity()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $sku = 'downloadable-product';
        $qty = 1;
        $finalQty = $qty + 1;
        $links = $this->getProductsLinks($sku);
        $linkId = key($links);

        $query = <<<MUTATION
mutation {
    addDownloadableProductsToCart(
        input: {
            cart_id: "{$maskedQuoteId}",
            cart_items: [
                {
                    data: {
                        quantity: {$qty},
                        sku: "{$sku}"
                    },
                    downloadable_product_links: [
                        {
          	                link_id: {$linkId}
                        }
                    ]
                }
            ]
        }
    ) {
        cart {
            items {
                product {
                    sku
                }
                quantity
            }
        }
    }
}
MUTATION;
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('items', $response['addDownloadableProductsToCart']['cart']);
        self::assertCount(1, $response['addDownloadableProductsToCart']['cart']['items']);
        self::assertEquals($finalQty, $response['addDownloadableProductsToCart']['cart']['items'][0]['quantity']);
        self::assertEquals($sku, $response['addDownloadableProductsToCart']['cart']['items'][0]['product']['sku']);
    }

    /**
     * Update a downloadable product into shopping cart when "Links can be purchased separately" is enabled
     *
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote_with_downloadable_product.php
     */
    public function testRemoveCartItemIfQuantityIsZero()
    {
        $reservedOrderId = "test_order_1";
        $sku = "downloadable-product";

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $qty = 0;

        $itemId = 0;
        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getSku() == $sku) {
                $itemId = $item->getId();
            }
        }

        $query = <<<MUTATION
mutation {
  updateCartItems(input: {
    cart_id: "{$maskedQuoteId}"
    cart_items:[
      {
        cart_item_id: {$itemId}
        quantity: {$qty}
      }
    ]
  }) {
    cart {
      items {
        id
        quantity
      }
    }
  }
}
MUTATION;
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('updateCartItems', $response);
        self::assertArrayHasKey('cart', $response['updateCartItems']);

        $responseCart = $response['updateCartItems']['cart'];
        self::assertCount(0, $responseCart['items']);
    }

    /**
     * Function returns array of all product's links
     *
     * @param string $sku
     * @return array
     */
    private function getProductsLinks(string $sku) : array
    {
        $result = [];
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        $product = $productRepository->get($sku, false, null, true);

        foreach ($product->getDownloadableLinks() as $linkObject) {
            $result[$linkObject->getLinkId()] = [
                'title' => $linkObject->getTitle(),
                'link_type' => null, //deprecated field
                'price' => $linkObject->getPrice(),
            ];
        }

        return $result;
    }
}
