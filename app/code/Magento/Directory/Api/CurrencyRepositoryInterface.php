<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api;

/**
 * Currency interface
 *
 * @api
 */
interface CurrencyRepositoryInterface
{
    /**
     * Get Currency (by Store: optional).
     *
     * @param int $storeId | null
     * @return \Magento\Directory\Api\Data\CurrencyInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $id is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrency($storeId = null);
}
