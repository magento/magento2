<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\QuoteSession;

/**
 * Interface QuoteSessionInterface
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
interface QuoteSessionInterface
{
    /**
     * Returns quote from session.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuote();
}
