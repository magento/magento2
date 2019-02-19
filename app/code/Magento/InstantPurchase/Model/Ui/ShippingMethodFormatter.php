<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\Ui;

use Magento\Quote\Api\Data\ShippingMethodInterface;

/**
 * Ship[ping method string presentation.
 *
 * @api May be used for pluginization.
 */
class ShippingMethodFormatter
{
    /**
     * @param ShippingMethodInterface $shippingMethod
     * @return string
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
