<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

class AddSimpleProductToCartTest extends GraphQlAbstract
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddSimpleProductToCart()
    {
        $sku = 'simple';
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteId();

        $query = $this->getAddSimpleProductQuery($maskedQuoteId, $sku, $qty);
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);

        self::assertEquals($qty, $response['addSimpleProductsToCart']['cart']['items'][0]['qty']);
        self::assertEquals($sku, $response['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage Please enter a number greater than 0 in this field.
     */
    public function testAddSimpleProductToCartWithNegativeQty()
    {
        $sku = 'simple';
        $qty = -2;
        $maskedQuoteId = $this->getMaskedQuoteId();

        $query = $this->getAddSimpleProductQuery($maskedQuoteId, $sku, $qty);
        $this->graphQlQuery($query);
    }

    /**
     * @return string
     */
    public function getMaskedQuoteId() : string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_1', 'reserved_order_id');

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $maskedQuoteId
     * @param string $sku
     * @param int $qty
     * @return string
     */
    public function getAddSimpleProductQuery(string $maskedQuoteId, string $sku, int $qty): string
    {
        return <<<QUERY
mutation {  
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cartItems: [
        {
          data: {
            qty: $qty
            sku: "$sku"
          }
        }
      ]
    }
  ) {
    cart {
      items {
        qty
        product {
          sku
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "wrong_cart_hash"
     */
    public function testAddProductWithWrongCartHash()
    {
        $sku = 'simple';
        $qty = 1;

        $maskedQuoteId = 'wrong_cart_hash';

        $query = <<<QUERY
mutation {  
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cartItems: [
        {
          data: {
            qty: $qty
            sku: "$sku"
          }
        }
      ]
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

        $this->graphQlQuery($query);
    }
}
