<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Tax\Model\Config\Source\Apply;

class On implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Custom price if available')],
            ['value' => 1, 'label' => __('Original price only')]
        ];
    }
}
