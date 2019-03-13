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
use Magento\QuoteGraphQl\Model\GetMaskedQuoteIdByReversedQuoteId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting cart information
 */
class GetAvailablePaymentMethodsTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReversedQuoteId
     */
    private $getMaskedQuoteIdByReversedQuoteId;

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
        $this->getMaskedQuoteIdByReversedQuoteId = $objectManager->get(GetMaskedQuoteIdByReversedQuoteId::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetCartWithPaymentMethods()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId->execute('test_order_with_simple_product_without_address');

        $query = <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    available_payment_methods {
      code
      title
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cart', $response);

        self::assertEquals('checkmo', $response['cart']['available_payment_methods'][0]['code']);
        self::assertEquals('Check / Money order', $response['cart']['available_payment_methods'][0]['title']);

        self::assertEquals('free', $response['cart']['available_payment_methods'][1]['code']);
        self::assertEquals(
            'No Payment Information Required',
            $response['cart']['available_payment_methods'][1]['title']
        );
    }
}
