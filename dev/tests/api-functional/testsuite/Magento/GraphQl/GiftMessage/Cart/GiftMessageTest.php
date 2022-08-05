<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GiftMessage\Cart;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GiftMessageTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoConfigFixture default_store sales/gift_options/allow_order 1
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_message.php
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function testGiftMessageForCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('message_order_21');
        $response = $this->requestCartAndAssertResult($maskedQuoteId);
        self::assertArrayHasKey('gift_message', $response['cart']);
        self::assertSame('Mercutio', $response['cart']['gift_message']['to']);
        self::assertSame('Romeo', $response['cart']['gift_message']['from']);
        self::assertSame('I thought all for the best.', $response['cart']['gift_message']['message']);
    }

    /**
     * @magentoConfigFixture default_store sales/gift_options/allow_order 0
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_message.php
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function testGiftMessageForCartWithNotAllow()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('message_order_21');
        $response = $this->requestCartAndAssertResult($maskedQuoteId);
        self::assertArrayHasKey('gift_message', $response['cart']);
        self::assertNull($response['cart']['gift_message']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function testGiftMessageForCartWithoutMessage()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $response = $this->requestCartAndAssertResult($maskedQuoteId);
        self::assertArrayHasKey('gift_message', $response['cart']);
        self::assertNull($response['cart']['gift_message']);
    }

    /**
     * Get Gift Message Assertion
     *
     * @param string $quoteId
     *
     * @return array
     * @throws Exception
     */
    private function requestCartAndAssertResult(string $quoteId)
    {
        $query =  <<<QUERY
{
    cart(cart_id: "$quoteId") {
        gift_message {
            to
            from
            message
        }
    }
}
QUERY;
        return $this->graphQlQuery($query);
    }
}
