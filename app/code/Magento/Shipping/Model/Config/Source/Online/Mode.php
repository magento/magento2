<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Shipping\Model\Config\Source\Online;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Shippers Modesource model
 */
class Mode implements OptionSourceInterface
{
    /**
     * Returns array to be used in packages request type on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Development')],
            ['value' => '1', 'label' => __('Live')]
        ];
    }
}
