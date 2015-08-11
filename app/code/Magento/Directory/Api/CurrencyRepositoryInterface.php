<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api;

/**
 * Currency Repository interface
 *
 * @api
 */
interface CurrencyRepositoryInterface
{
    /**
     * Get currency information for the store.
     *
     * @return \Magento\Directory\Api\Data\CurrencyInformationInterface
     */
    public function getCurrencyInfo();
}
