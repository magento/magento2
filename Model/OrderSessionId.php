<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Checkout\Model\Session;

/**
 * Class OrderSessionId
 */
class OrderSessionId
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Generate the unique ID for the user's browsing session
     *
     * @return string
     */
    public function generate()
    {
        return sha1($this->getQuote()->getId() . $this->getQuote()->getCreatedAt());
    }

    /**
     * Get current quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }
}
