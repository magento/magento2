<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftCardGraphQl\Model\Resolver\GiftCardCartItem;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GiftCard\Model\Giftcard\Option as GiftcardOption;
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;

/**
 * Amount resolver for Giftcard Cart Item
 */
class GiftCardAmount implements ResolverInterface
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * GiftCardAmount constructor.
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(PriceCurrency $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof CartItemInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var QuoteItem $cartItem */
        $cartItem = $value['model'];
        /** @var QuoteItemOption $amountOption */
        $amountOption = $cartItem->getOptionByCode(GiftcardOption::KEY_AMOUNT);

        return [
            'value' => floatval($amountOption->getValue()),
            'currency' => $cartItem->getQuote()->getQuoteCurrencyCode(),
            'formatted' => $this->priceCurrency->format(floatval($amountOption->getValue()),false,null,null,$cartItem->getQuote()->getQuoteCurrencyCode())
        ];
    }
}
