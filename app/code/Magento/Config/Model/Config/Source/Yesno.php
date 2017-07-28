<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source;

/**
 * @api
 * @since 2.0.0
 */
class Yesno implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Yes')], ['value' => 0, 'label' => __('No')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     * @since 2.0.0
     */
    public function toArray()
    {
        return [0 => __('No'), 1 => __('Yes')];
    }
}
