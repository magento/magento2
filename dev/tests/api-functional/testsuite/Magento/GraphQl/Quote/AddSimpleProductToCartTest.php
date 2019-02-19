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
use Magento\Config\Model\ResourceModel\Config;

class AddSimpleProductToCartTest extends GraphQlAbstract
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
     * @var Config
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->config = $objectManager->get(Config::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddSimpleProductsToCart()
    {
        $sku = 'simple';
        $qty = 2;
        $maskedQuoteId = $this->getMaskedQuoteId();
        $query = $this->getQueryAddSimpleProduct($maskedQuoteId, $sku, $qty);
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);
        $cartQty = $response['addSimpleProductsToCart']['cart']['items'][0]['qty'];

        $this->assertEquals($qty, $cartQty);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php

     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage The most you may purchase is 5.
     */
    public function testAddMoreProductsThatAllowed()
    {
        $sku = 'simple';
        $qty = 7;
        $maxQty = 5;

        $this->config->saveConfig('cataloginventory/item_options/max_sale_qty', $maxQty, 'default', 0);
        $maskedQuoteId = $this->getMaskedQuoteId();
        $query = $this->getQueryAddSimpleProduct($maskedQuoteId, $sku, $qty);
        $this->graphQlQuery($query);
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
        $query = $this->getQueryAddSimpleProduct($maskedQuoteId, $sku, $qty);
        $this->graphQlQuery($query);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMaskedQuoteId() : string
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_1',
            'reserved_order_id'
        );
        return $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
    }

    /**
     * @param string $maskedQuoteId
     * @param string $sku
     * @param int $qty
     *
     * @return string
     */
    public function getQueryAddSimpleProduct(string $maskedQuoteId, string $sku, int $qty) : string
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
      cart_id
      items {
        qty
      }
    }
  }
}
QUERY;
    }
}
