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

class addConfigurableProductsToCartTest extends GraphQlAbstract
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
        $this->quoteResource =  $objectManager->create(QuoteResource::class);
        $this->quote =  $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId =  $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products_2.php
     */
    public function testAddConfigurableProductsToCart()
    {
        $variantSku = 'simple_41';
        $qty = 200;
        $expectedMessage = 'GraphQL response contains errors: The requested qty is not available
';
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $query = $this->prepareAddConfigurableProductsToCartQuery($maskedQuoteId, $variantSku, $qty);

        try {
            $this->graphQlQuery($query);
        } catch (\Exception $exception) {
            $this->assertEquals(
                $expectedMessage,
                $exception->getMessage()
            );
        }
    }

    /**
     * @param string $maskedQuoteId
     * @param string $variantSku
     * @param int $qty
     *
     * @return string
     */
    public function prepareAddConfigurableProductsToCartQuery(string $maskedQuoteId, string $variantSku, int $qty)
    {
        return <<<QUERY
mutation {
  
  addConfigurableProductsToCart(
    input: {
      cart_id: "$maskedQuoteId", 
      cartItems: [
        {
          variant_sku: "$variantSku"
          data: {
            qty: $qty
            sku: "$variantSku"
          }
        }
      ]
    }
  ) {
    cart {
      items {
        id
        qty
        product {
          name
          sku
        }
      }
    }
  }
}

QUERY;
    }
}
