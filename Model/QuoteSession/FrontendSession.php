<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\QuoteSession;

use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Implementation of QuoteSessionInterface for Magento frontend checkout.
 * @since 2.2.0
 */
class FrontendSession implements QuoteSessionInterface
{
    /**
     * @var CheckoutSession
     * @since 2.2.0
     */
    private $checkoutSession;

    /**
     * FrontendSession constructor.
     *
     * Class uses checkout session for retrieving quote.
     *
     * @param CheckoutSession $checkoutSession
     * @since 2.2.0
     */
    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
