<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Config\Source\Online;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for Shippers Request Type
 * @since 2.0.0
 */
class Requesttype implements OptionSourceInterface
{
    /**
     * Returns array to be used in packages request type on back-end
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Divide to equal weight (one request)')],
            ['value' => 1, 'label' => __('Use origin weight (few requests)')]
        ];
    }
}
