<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\Order\OrderPayments;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\Quote;

/**
 * @inheritdoc
 */
class QuoteOrderPlaceRedirectUrl implements ResolverInterface
{
    /**
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        private readonly QuoteRepository $quoteRepository
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model']) || !($value['model'] instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var OrderInterface $order */
        $order = $value['model'];

        if (!$order->getQuoteId()) {
            return;
        }

        /** @var ?Quote $quote */
        $quote = $this->quoteRepository->get($order->getQuoteId());

        if (!$quote) {
            return;
        }

        return $quote->getPayment()->getOrderPlaceRedirectUrl();
    }
}

