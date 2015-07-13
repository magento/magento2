<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Source;

use \Magento\Braintree\Model\PaymentMethod;

/**
 * Class CaptureAction
 * @codeCoverageIgnore
 */
class CaptureAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible actions to capture
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => PaymentMethod::CAPTURE_ON_INVOICE,
                'label' => __('Invoice'),
            ],
            [
                'value' => PaymentMethod::CAPTURE_ON_SHIPMENT,
                'label' => __('Shipment'),
            ],
        ];
    }
}
