<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for merging guest carts
 */
class MergeCartsTest extends GraphQlAbstract
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

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/simple_product.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testMergeGuestCarts()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $firstQuote = $this->quoteFactory->create();
        $this->quoteResource->load($firstQuote, 'test_order_with_simple_product_without_address', 'reserved_order_id');

        $secondQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $secondQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );

        $firstMaskedId = $this->quoteIdToMaskedId->execute((int)$firstQuote->getId());
        $secondMaskedId = $this->quoteIdToMaskedId->execute((int)$secondQuote->getId());

        $query = $this->getCartMergeMutation($firstMaskedId, $secondMaskedId);
        $this->graphQlMutation($query);
    }

    /**
     * Create the mergeCart mutation
     *
     * @param string $guestQuoteMaskedId
     * @param string $customerQuoteMaskedId
     * @return string
     */
    private function getCartMergeMutation(string $guestQuoteMaskedId, string $customerQuoteMaskedId): string
    {
        return <<<QUERY
mutation {
  mergeCarts(
    source_cart_id: "{$guestQuoteMaskedId}"
    destination_cart_id: "{$customerQuoteMaskedId}"
  ){
  items {
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }
}
