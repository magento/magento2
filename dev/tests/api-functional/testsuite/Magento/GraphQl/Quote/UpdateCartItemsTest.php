<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for updating/removing shopping cart items
 */
class UpdateCartItemsTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $this->objectManager->create(QuoteResource::class);
        $this->quote = $this->objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testUpdateCartItemQty()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $quoteItem = $this->quote->getItemByProduct($this->productRepository->get('simple'));
        $qty = $quoteItem->getQty() + 2;

        $query = $this->prepareUpdateItemsQuery($maskedQuoteId, (string) $quoteItem->getItemId(), $qty);
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('cart', $response['updateCartItems']);

        $responseCart = $response['updateCartItems']['cart'];
        $item = current($responseCart['items']);

        $this->assertEquals($quoteItem->getItemId(), $item['id']);
        $this->assertEquals($qty, $item['qty']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testRemoveCartItemByZeroQuantityUpdate()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $quoteItem = $this->quote->getItemByProduct($this->productRepository->get('simple'));

        $query = $this->prepareUpdateItemsQuery($maskedQuoteId, (string) $quoteItem->getItemId(), 0);
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('cart', $response['updateCartItems']);

        $responseCart = $response['updateCartItems']['cart'];
        $this->assertCount(0, $responseCart['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find cart item with id
     */
    public function testUpdateCartItemNoSuchItemEntity()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareUpdateItemsQuery($maskedQuoteId, '999', 4);
        $this->graphQlQuery($query);
    }

    /**
     * Test mutation is only able to update quote items belonging to the requested cart
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find cart item with id
     */
    public function testUpdateItemFromDifferentQuote()
    {
        /** @var Quote $secondQuote */
        $secondQuote = $this->objectManager->create(Quote::class);
        $this->quoteResource->load(
            $secondQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $secondQuoteItem = $secondQuote->getItemByProduct($this->productRepository->get('virtual-product'));

        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = $this->prepareUpdateItemsQuery($maskedQuoteId, $secondQuoteItem->getId(), 4);
        $this->graphQlQuery($query);
    }

    private function prepareUpdateItemsQuery(string $maskedQuoteId, string $itemId, float $qty): string
    {
        return <<<QUERY
mutation {
  updateCartItems(input:{
    cart_id:"$maskedQuoteId"
    cart_items:[
      {
        item_id:"$itemId"
        qty: $qty
      }
    ]
  }) {
    cart {
      cart_id
      items {
        id
        qty
      }
    }
  }
}
QUERY;
    }
}
