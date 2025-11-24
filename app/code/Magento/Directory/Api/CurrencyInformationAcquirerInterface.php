<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Directory\Api;

/**
 * Currency information acquirer interface
 *
 * @api
 * @since 100.0.2
 */
interface CurrencyInformationAcquirerInterface
{
    /**
     * Get currency information for the store.
     *
     * @return \Magento\Directory\Api\Data\CurrencyInformationInterface
     */
    public function getCurrencyInfo();
}
