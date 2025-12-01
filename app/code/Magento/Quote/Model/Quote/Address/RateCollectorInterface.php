<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
