<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

/**
 * Extract data from cart
 */
class ExtractDataFromCart
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     */
    public function __construct(
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
    }

    /**
     * Extract data from cart
     *
     * @param Quote $cart
     * @return array
     * @throws NoSuchEntityException
     */
    public function execute(Quote $cart): array
    {
        $items = [];

        /**
         * @var QuoteItem $cartItem
         */
        foreach ($cart->getAllItems() as $cartItem) {
            $productData = $cartItem->getProduct()->getData();
            $productData['model'] = $cartItem->getProduct();

            $items[] = [
                'id' => $cartItem->getItemId(),
                'qty' => $cartItem->getQty(),
                'product' => $productData,
                'model' => $cartItem,
            ];
        }

        $appliedCoupon = $cart->getCouponCode();

        return [
            'cart_id' => $this->quoteIdToMaskedQuoteId->execute((int)$cart->getId()),
            'items' => $items,
            'applied_coupon' => $appliedCoupon ? ['code' => $appliedCoupon] : null
        ];
    }
}
