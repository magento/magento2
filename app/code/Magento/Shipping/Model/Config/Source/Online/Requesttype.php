<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Config\Source\Online;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for Shippers Request Type
 */
class Requesttype implements OptionSourceInterface
{
    /**
     * Returns array to be used in packages request type on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Divide to equal weight (one request)')],
            ['value' => 1, 'label' => __('Use origin weight (few requests)')]
        ];
    }
}
