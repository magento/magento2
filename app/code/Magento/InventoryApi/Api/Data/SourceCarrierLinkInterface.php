<?php
/**
 * Created by PhpStorm.
 * User: nhp
 * Date: 5/20/17
 * Time: 2:52 PM
 */

namespace Magento\InventoryApi\Api\Data;


use Magento\Shipping\Model\Carrier\CarrierInterface;

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