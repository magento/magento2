<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\QuoteSession;

use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Implementation of QuoteSessionInterface for Magento frontend checkout.
 */
class FrontendSession implements QuoteSessionInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * FrontendSession constructor.
     *
     * Class uses checkout session for retrieving quote.
     *
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritdoc
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
