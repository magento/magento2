<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

class AddDownloadableProductToCartTest extends GraphQlAbstract
{
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * Test adding a downloadable product to the shopping cart
     *
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDownloadableProduct()
    {
        $sku = 'downloadable-product';
        $qty = 1;

        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<MUTATION
mutation {
  addDownloadableProductsToCart(
    input: {
        cart_id: "{$maskedQuoteId}", 
        cartItems: [
            {
                data: {
                    qty: {$qty}, 
                    sku: "{$sku}"
                }, 
                customizable_options: []
            }
        ]
    }
  ) {
    cart {
      items {
        id
      }
    }
  }
}
MUTATION;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('items', $response['addDownloadableProductsToCart']['cart']);
        self::assertCount(1, $response['addDownloadableProductsToCart']['cart']);
    }
}
