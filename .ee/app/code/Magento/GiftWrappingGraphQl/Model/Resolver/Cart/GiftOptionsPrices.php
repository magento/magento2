<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftWrappingGraphQl\Model\Resolver\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

class GiftOptionsPrices implements ResolverInterface
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * GiftOptionsPrices constructor.
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(PriceCurrency $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get information about gift wrapping prices
     *
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return Value|mixed|void
     *
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof CartInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Quote $quote */
        $quote = $value['model'];
        $currency = $quote->getQuoteCurrencyCode();

        return [
            'gift_wrapping_for_order' => ['value' => $quote->getGwPrice(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($quote->getGwPrice(),false,null,null,$currency)],
            'gift_wrapping_for_items' => ['value' => $quote->getGwItemsPrice(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($quote->getGwItemsPrice(),false,null,null,$currency)],
            'printed_card' => ['value' =>  $quote->getGwCardPrice(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($quote->getGwCardPrice(),false,null,null,$currency)]
        ];
    }
}
