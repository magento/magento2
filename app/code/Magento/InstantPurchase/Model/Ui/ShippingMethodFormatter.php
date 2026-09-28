<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\InstantPurchase\Model\Ui;

use Magento\Quote\Api\Data\ShippingMethodInterface;

/**
 * Ship[ping method string presentation.
 *
 * @api May be used for pluginization.
 * @since 100.2.0
 */
class ShippingMethodFormatter
{
    /**
     * @param ShippingMethodInterface $shippingMethod
     * @return string
     * @since 100.2.0
     */
    public function format(ShippingMethodInterface $shippingMethod) : string
    {
        $data = [
            $shippingMethod->getCarrierTitle(),
            $shippingMethod->getMethodTitle(),
        ];
        $data = array_filter($data);
        $formatted = join(' - ', $data);
        return $formatted;
    }
}
