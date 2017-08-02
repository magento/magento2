<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config\Source\Apply;

/**
 * Class \Magento\Tax\Model\Config\Source\Apply\On
 *
 * @since 2.0.0
 */
class On implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Custom price if available')],
            ['value' => 1, 'label' => __('Original price only')]
        ];
    }
}
