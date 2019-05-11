<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Quote\Model\Quote;

class CheckCartCheckoutAllowance
{
    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @param CheckoutHelper $checkoutHelper
     */
    public function __construct(
        CheckoutHelper $checkoutHelper
    ) {
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * Check if User is allowed to checkout
     *
     * @param Quote $quote
     * @return void
     * @throws GraphQlAuthorizationException
     */
    public function execute(Quote $quote): void
    {
        if (false === $quote->getCustomerIsGuest()) {
            return;
        }

        $isAllowedGuestCheckout = $this->checkoutHelper->isAllowedGuestCheckout($quote);
        if (false === $isAllowedGuestCheckout) {
            throw new GraphQlAuthorizationException(__('Guest checkout is not allowed.'));
        }
    }
}
