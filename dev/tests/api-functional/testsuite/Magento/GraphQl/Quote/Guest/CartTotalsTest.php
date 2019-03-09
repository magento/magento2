<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test getting cart totals for guest
 */
class CartTotalsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

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

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->create(QuoteResource::class);
        $this->quoteFactory = $objectManager->create(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->create(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_tax_guest.php
     */
    public function testGetCartTotalsForCustomerWithTaxApplied()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_tax');

        $query = <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    prices {
      grand_total {
        value,
        currency
      }
      subtotal_including_tax {
        value
        currency
      }
      subtotal_excluding_tax {
        value
        currency
      }
      subtotal_with_discount_excluding_tax {
        value
        currency
      }
      applied_taxes {
        label
        amount {
          value
          currency
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('prices', $response['cart']);
        $pricesResponse = $response['cart']['prices'];
        self::assertEquals(10.83, $pricesResponse['grand_total']['value']);
        self::assertEquals(10.83, $pricesResponse['subtotal_including_tax']['value']);
        self::assertEquals(10, $pricesResponse['subtotal_excluding_tax']['value']);
        self::assertEquals(10, $pricesResponse['subtotal_with_discount_excluding_tax']['value']);

        $appliedTaxesResponse = $pricesResponse['applied_taxes'];

        self::assertEquals('US-CA-*-Rate 1', $appliedTaxesResponse[0]['label']);
        self::assertEquals(0.83, $appliedTaxesResponse[0]['amount']['value']);
        self::assertEquals('USD', $appliedTaxesResponse[0]['amount']['currency']);
    }

    /**
     * @param string $reversedQuoteId
     * @return string
     */
    private function getMaskedQuoteIdByReversedQuoteId(string $reversedQuoteId): string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }
}
