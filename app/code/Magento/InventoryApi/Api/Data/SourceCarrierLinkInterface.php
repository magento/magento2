<?php
/**
 * Created by PhpStorm.
 * User: nhp
 * Date: 5/20/17
 * Time: 2:52 PM
 */

namespace Magento\InventoryApi\Api\Data;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Linkage interface between sources and carrier.
 * @api
 */
interface SourceCarrierLinkInterface
{
    /**
     * Set source.
     *
     * @param SourceInterface $source
     * @return void
     */
    public function setSource(SourceInterface $source);

    /**
     * Set carrier.
     *
     * @param CarrierInterface $carrier
     * @return void
     */
    public function setCarrier(CarrierInterface $carrier);

    /**
     * Get link.
     *
     * @return $this
     */
    public function getSourceCarrierLink();
}
