<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for assigning guest to the guest cart
 */
class AssignCustomerToGuestCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
    }

    /**
     * Test for assigning guest to the guest cart
     *
     * @magentoApiDataFixture Magento/Checkout/_files/simple_product.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testAssignCustomerToGuestCartForGuest(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $guestQuote = $this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address');
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());

        $query = $this->getAssignCustomerToGuestCartMutation($guestQuoteMaskedId);
        $this->graphQlMutation($query);
    }

    /**
     * Create the assignCustomerToGuestCart mutation
     *
     * @param string $guestQuoteMaskedId
     * @return string
     */
    private function getAssignCustomerToGuestCartMutation(string $guestQuoteMaskedId): string
    {
        return <<<QUERY
mutation {
  assignCustomerToGuestCart(
    cart_id: "{$guestQuoteMaskedId}"
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
