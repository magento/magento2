<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source;

/**
 * Source model for element with enable and disable variants.
 * @api
 * @since 2.0.0
 */
class Enabledisable implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Value which equal Enable for Enabledisable dropdown.
     */
    const ENABLE_VALUE = 1;

    /**
     * Value which equal Disable for Enabledisable dropdown.
     */
    const DISABLE_VALUE = 0;

    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ENABLE_VALUE, 'label' => __('Enable')],
            ['value' => self::DISABLE_VALUE, 'label' => __('Disable')],
        ];
    }
}
