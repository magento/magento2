<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class PaymentAction
 */
class PaymentAction implements ArrayInterface
{
    /**
     * Possible actions on order place
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize'),
            ],
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture'),
            ]
        ];
    }
}
