<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;

/**
 * Interface CarrierFactoryInterface
 */
interface CarrierFactoryInterface
{
    /**
     * Get carrier instance
     *
     * @param string $carrierCode
     * @return bool|AbstractCarrierInterface
     * @api
     */
    public function get($carrierCode);

    /**
     * Create carrier instance
     *
     * @param string $carrierCode
     * @param int|null $storeId
     * @return bool|AbstractCarrierInterface
     * @api
     */
    public function create($carrierCode, $storeId = null);

    /**
     * Get carrier by its code if it is active
     *
     * @param string $carrierCode
     * @return bool|AbstractCarrierInterface
     * @api
     */
    public function getIfActive($carrierCode);

    /**
     * Create carrier by its code if it is active
     *
     * @param string $carrierCode
     * @param null|int $storeId
     * @return bool|AbstractCarrierInterface
     * @api
     */
    public function createIfActive($carrierCode, $storeId = null);
}
