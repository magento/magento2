<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryApi\Api\Data;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Interface SourceCarrierLinkInterface
 * @package Magento\InventoryApi\Api\Data
 */
interface SourceCarrierLinkInterface
{
    /**
     * @param SourceInterface $source
     * @return void
     */
    public function setSource(SourceInterface $source);

    /**
     * @param CarrierInterface $carrier
     * @return void
     */
    public function setCarrier(CarrierInterface $carrier);

    /**
     * @return $this
     */
    public function getSourceCarrierLink();
}