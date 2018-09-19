<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * @api
 * @since 100.0.2
 */
interface RateCollectorInterface
{
    /**
     * @param RateRequest $request
     * @return $this
     */
    public function collectRates(RateRequest $request);
}
