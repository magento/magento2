<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Authorize.net Payment Action Dropdown source
 * @deprecated 100.3.1 Authorize.net is removing all support for this payment method
 */
class PaymentAction implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\Authorizenet\Model\Authorizenet::ACTION_AUTHORIZE,
                'label' => __('Authorize Only'),
            ],
            [
                'value' => \Magento\Authorizenet\Model\Authorizenet::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture')
            ]
        ];
    }
}
