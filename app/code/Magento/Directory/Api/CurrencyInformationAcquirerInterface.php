<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
