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

class AddConfigurableProductToCartTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductToCart()
    {
        $variantSku = 'simple_41';
        $qty = 2;

        $maskedQuoteId = $this->getMaskedQuoteId();

        $query = $this->getAddConfigurableProductMutationQuery($maskedQuoteId, $variantSku, $qty);

        $response = $this->graphQlQuery($query);
        $cartItems = $response['addConfigurableProductsToCart']['cart']['items'];
        self::assertEquals($qty, $cartItems[0]['qty']);
        self::assertEquals($variantSku, $cartItems[0]['product']['sku']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_mixed_products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage The requested qty is not available
     */
    public function testAddProductIfQuantityIsNotAvailable()
    {
        $variantSku = 'simple_41';
        $qty = 200;

        $maskedQuoteId = $this->getMaskedQuoteId();
        $query = $this->getAddConfigurableProductMutationQuery($maskedQuoteId, $variantSku, $qty);

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Framework/Search/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage Product that you are trying to add is not available.
     */
    public function testAddOutOfStockProduct()
    {
        $variantSku = 'simple_1010';
        $qty = 1;
        $maskedQuoteId = $this->getMaskedQuoteId();
        $query = $this->getAddConfigurableProductMutationQuery($maskedQuoteId, $variantSku, $qty);

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getMaskedQuoteId()
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
    private function getAddConfigurableProductMutationQuery(string $maskedQuoteId, string $variantSku, int $qty): string
    {
        return <<<QUERY
mutation {
  addConfigurableProductsToCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cartItems: [
        {
          variant_sku: "{$variantSku}"
          data: {
            qty: {$qty}
            sku: "{$variantSku}"
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
        ... on ConfigurableCartItem {
          configurable_options {
            option_label
          }
        }
    }
  }
  }
}
QUERY;
    }
}
