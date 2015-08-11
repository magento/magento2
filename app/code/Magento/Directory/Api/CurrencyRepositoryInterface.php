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
     * @return \Magento\Directory\Api\Data\CurrencyInformationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $id is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrencyInfo();
}
