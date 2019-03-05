<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogInventory;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

class AddProductToCartTest extends GraphQlAbstract
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
     * @expectedException \Exception
     * @expectedExceptionMessage The requested qty is not available
     */
    public function testAddProductIfQuantityIsNotAvailable()
    {
        $sku = 'simple';
        $qty = 200;

        $maskedQuoteId = $this->getMaskedQuoteId();
        $query = $this->getAddSimpleProductQuery($maskedQuoteId, $sku, $qty);
        $this->graphQlQuery($query);
        self::fail('Should be "The requested qty is not available" error message.');
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoConfigFixture default cataloginventory/item_options/max_sale_qty 5
     * @expectedException \Exception
     * @expectedExceptionMessage The most you may purchase is 5.
     */
    public function testAddMoreProductsThatAllowed()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/167');

        $sku = 'custom-design-simple-product';
        $qty = 7;

        $maskedQuoteId = $this->getMaskedQuoteId();
        $query = $this->getAddSimpleProductQuery($maskedQuoteId, $sku, $qty);
        $this->graphQlQuery($query);
        self::fail('Should be "The most you may purchase is 5." error message.');
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
    public function getAddSimpleProductQuery(string $maskedQuoteId, string $sku, int $qty) : string
    {
        return <<<QUERY
mutation {  
  addSimpleProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}", 
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
    }
}
