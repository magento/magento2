<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Gender implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Male')],
            ['value' => 2, 'label' => __('Female')],
        ];
    }
}
