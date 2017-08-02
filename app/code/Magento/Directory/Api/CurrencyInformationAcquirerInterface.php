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
 * @since 2.0.0
 */
interface CurrencyInformationAcquirerInterface
{
    /**
     * Get currency information for the store.
     *
     * @return \Magento\Directory\Api\Data\CurrencyInformationInterface
     * @since 2.0.0
     */
    public function getCurrencyInfo();
}
