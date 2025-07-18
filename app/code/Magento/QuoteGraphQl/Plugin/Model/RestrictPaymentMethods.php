<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin\Model;

use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\Data\CartInterface;

class RestrictPaymentMethods
{
    /**
     * Show only the "free" payment method if the order total is 0
     *
     * @param MethodList $subject
     * @param array $result
     * @param CartInterface|null $quote
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAvailableMethods(
        MethodList $subject,
        array $result,
        ?CartInterface $quote = null
    ): array {
        if (!$quote || $quote->getGrandTotal() != 0) {
            return $result;
        }

        return array_filter($result, fn ($method) => $method->getCode() === 'free') ?: [];
    }
}
