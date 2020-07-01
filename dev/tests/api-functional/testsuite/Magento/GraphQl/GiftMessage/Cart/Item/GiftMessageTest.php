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

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoConfigFixture default_store sales/gift_options/allow_items 0
     * @magentoApiDataFixture Magento/GiftMessage/_files/guest/quote_with_item_message.php
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function testGiftMessageCartForItemNotAllow()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_guest_order_with_gift_message');
        foreach ($this->requestCartResult($maskedQuoteId)['cart']['items'] as $item) {
            self::assertArrayHasKey('gift_message', $item);
            self::assertNull($item['gift_message']);
        }
    }

    /**
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     * @magentoApiDataFixture Magento/GiftMessage/_files/guest/quote_with_item_message.php
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function testGiftMessageCartForItem()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_guest_order_with_gift_message');
        foreach ($this->requestCartResult($maskedQuoteId)['cart']['items'] as $item) {
            self::assertArrayHasKey('gift_message', $item);
            self::assertArrayHasKey('to', $item['gift_message']);
            self::assertArrayHasKey('from', $item['gift_message']);
            self::assertArrayHasKey('message', $item['gift_message']);
        }
    }

    /**
     * @param string $quoteId
     *
     * @return array|bool|float|int|string
     * @throws Exception
     */
    private function requestCartResult(string $quoteId)
    {
        $query = <<<QUERY
{
    cart(cart_id: "$quoteId") {
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
        return $this->graphQlQuery($query);
    }
}
