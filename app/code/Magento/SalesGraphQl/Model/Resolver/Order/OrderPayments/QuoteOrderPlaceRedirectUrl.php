<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\Order\OrderPayments;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;

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
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): ?string
    {
        if (!isset($value['model']) || !($value['model'] instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var OrderInterface $order */
        $order = $value['model'];

        if (!$order->getQuoteId()) {
            return null;
        }

        try {
            /** @var ?Quote $quote */
            $quote = $this->quoteRepository->get($order->getQuoteId());
        } catch (NoSuchEntityException) {
            return null;
        }

        if (!$quote) {
            return null;
        }

        $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();

        if ($redirectUrl === '') {
            return null;
        }

        return $redirectUrl;
    }
}

