<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\QuoteFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for removeItemFromCartTest mutation
 */
class RemoveItemFromCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

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
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testRemoveItemFromCart()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_with_simple_product_without_address', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $itemId = (int)$quote->getItemByProduct($this->productRepository->get('simple'))->getId();

        $query = $this->prepareMutationQuery($maskedQuoteId, $itemId);
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('removeItemFromCart', $response);
        $this->assertArrayHasKey('cart', $response['removeItemFromCart']);
        $this->assertCount(0, $response['removeItemFromCart']['cart']['items']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testRemoveItemFromNonExistentCart()
    {
        $query = $this->prepareMutationQuery('non_existent_masked_id', 1);
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testRemoveNonExistentItem()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_with_simple_product_without_address', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $notExistentItemId = 999;

        $this->expectExceptionMessage("Cart doesn't contain the {$notExistentItemId} item.");

        $query = $this->prepareMutationQuery($maskedQuoteId, $notExistentItemId);
        $this->graphQlQuery($query);
    }

    /**
     * Test mutation is only able to remove quote item belonging to the requested cart
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testRemoveItemIfItemIsNotBelongToCart()
    {
        $firstQuote = $this->quoteFactory->create();
        $this->quoteResource->load($firstQuote, 'test_order_with_simple_product_without_address', 'reserved_order_id');
        $firstQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$firstQuote->getId());

        $secondQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $secondQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $secondQuoteItemId = (int)$secondQuote
            ->getItemByProduct($this->productRepository->get('virtual-product'))
            ->getId();

        $this->expectExceptionMessage("Cart doesn't contain the {$secondQuoteItemId} item.");

        $query = $this->prepareMutationQuery($firstQuoteMaskedId, $secondQuoteItemId);
        $this->graphQlQuery($query);
    }

    /**
     * Test mutation is only able to remove quote item belonging to the requested cart
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testRemoveItemFromCustomerCart()
    {
        $customerQuote = $this->quoteFactory->create();
        $this->quoteResource->load($customerQuote, 'test_order_1', 'reserved_order_id');
        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());
        $customerQuoteItemId = (int)$customerQuote->getItemByProduct($this->productRepository->get('simple'))->getId();

        $this->expectExceptionMessage("The current user cannot perform operations on cart \"$customerQuoteMaskedId\"");

        $query = $this->prepareMutationQuery($customerQuoteMaskedId, $customerQuoteItemId);
        $this->graphQlQuery($query);
    }

    /**
     * @param string $input
     * @param string $message
     * @dataProvider dataProviderUpdateWithMissedRequiredParameters
     */
    public function testUpdateWithMissedItemRequiredParameters(string $input, string $message)
    {
        $query = <<<QUERY
mutation {
  removeItemFromCart(
    input: {
      {$input}
    }
  ) {
    cart {
      items {
        qty
      }
    }
  }
}
QUERY;
        $this->expectExceptionMessage($message);
        $this->graphQlQuery($query);
    }

    /**
     * @return array
     */
    public function dataProviderUpdateWithMissedRequiredParameters(): array
    {
        return [
            'missed_cart_id' => [
                'cart_item_id: 1',
                'Required parameter "cart_id" is missing.'
            ],
            'missed_cart_item_id' => [
                'cart_id: "test"',
                'Required parameter "cart_item_id" is missing.'
            ],
        ];
    }

    /**
     * @param string $maskedQuoteId
     * @param int $itemId
     * @return string
     */
    private function prepareMutationQuery(string $maskedQuoteId, int $itemId): string
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
        qty
      }
    }
  }
}
QUERY;
    }
}
