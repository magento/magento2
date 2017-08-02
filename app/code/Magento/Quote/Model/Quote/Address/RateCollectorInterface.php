<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * @api
 * @since 2.0.0
 */
interface RateCollectorInterface
{
    /**
     * @param RateRequest $request
     * @return $this
     * @since 2.0.0
     */
    public function collectRates(RateRequest $request);
}
