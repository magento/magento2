<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GiftMessage\Cart\Item;

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

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/guest/quote_with_item_message.php
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function testGiftMessageCartForItem()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_guest_order_with_gift_message');
        $query = <<<QUERY
{
    cart(cart_id: "$maskedQuoteId") {
        items {
            product {
                name
            }
            ... on SimpleCartItem {
                gift_message {
                    to
                    from
                    message
                }
            }
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        foreach ($response['cart']['items'] as $item) {
            self::assertArrayHasKey('gift_message', $item);
            self::assertArrayHasKey('to', $item['gift_message']);
            self::assertArrayHasKey('from', $item['gift_message']);
            self::assertArrayHasKey('message', $item['gift_message']);
        }
    }
}
