<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GiftMessage;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GiftMessageTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_message.php
     */
    public function testGiftMessageForCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = <<<QUERY
{
    cart(cart_id: "$maskedQuoteId") {
        gift_message {
            to
            from
            message
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('gift_message', $response['cart']);
        self::assertArrayHasKey('to', $response['cart']['gift_message']);
        self::assertArrayHasKey('from', $response['cart']['gift_message']);
        self::assertArrayHasKey('message', $response['cart']['gift_message']);
    }
}
