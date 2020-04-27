<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\QuoteSession\Adminhtml;

use Magento\Backend\Model\Session\Quote as BackendQuoteSession;
use Magento\Signifyd\Model\QuoteSession\QuoteSessionInterface;

/**
 * Implementation of QuoteSessionInterface for Magento backend checkout.
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class BackendSession implements QuoteSessionInterface
{
    /**
     * @var BackendQuoteSession
     */
    private $backendQuoteSession;

    /**
     * BackendSession constructor.
     *
     * Class uses backend session for retrieving quote.
     *
     * @param BackendQuoteSession $backendQuoteSession
     */
    public function __construct(BackendQuoteSession $backendQuoteSession)
    {
        $this->backendQuoteSession = $backendQuoteSession;
    }

    /**
     * @inheritdoc
     */
    public function getQuote()
    {
        return $this->backendQuoteSession->getQuote();
    }
}
