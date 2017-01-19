<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\QuoteSession;

/**
 * Interface QuoteSessionInterface
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
