<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Validator as OrderAddressValidator;

/**
 * Validates quote and order before quote submit.
 */
class SubmitQuoteValidator
{
    /**
     * @var QuoteValidator
     */
    private $quoteValidator;

    /**
     * @var OrderAddressValidator
     */
    private $orderAddressValidator;

    /**
     * @param QuoteValidator $quoteValidator
     * @param OrderAddressValidator $orderAddressValidator
     */
    public function __construct(
        QuoteValidator $quoteValidator,
        OrderAddressValidator $orderAddressValidator
    ) {
        $this->quoteValidator = $quoteValidator;
        $this->orderAddressValidator = $orderAddressValidator;
    }

    /**
     * Validates quote.
     *
     * @param Quote $quote
     * @return void
     * @throws LocalizedException
     */
    public function validateQuote(Quote $quote): void
    {
        $this->quoteValidator->validateBeforeSubmit($quote);
    }

    /**
     * Validates order.
     *
     * @param Order $order
     * @return void
     * @throws LocalizedException
     */
    public function validateOrder(Order $order): void
    {
        foreach ($order->getAddresses() as $address) {
            $errors = $this->orderAddressValidator->validate($address);
            if (!empty($errors)) {
                throw new LocalizedException(
                    __("Failed address validation:\n%1", implode("\n", $errors))
                );
            }
        }
    }
}
