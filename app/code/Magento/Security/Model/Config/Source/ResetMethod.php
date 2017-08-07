<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Model\Config\Source;

/**
 * Source model for setting "Limit Password Reset Requests Method"
 *
 * @since 2.1.0
 */
class ResetMethod implements \Magento\Framework\Option\ArrayInterface
{
    const OPTION_BY_IP_AND_EMAIL = 1;
    const OPTION_BY_IP = 2;
    const OPTION_BY_EMAIL = 3;
    const OPTION_NONE = 0;

    /**
     * Options getter
     *
     * @return array
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OPTION_BY_IP_AND_EMAIL, 'label' => __('By IP and Email')],
            ['value' => self::OPTION_BY_IP, 'label' => __('By IP')],
            ['value' => self::OPTION_BY_EMAIL, 'label' => __('By Email')],
            ['value' => self::OPTION_NONE, 'label' => __('None')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     * @since 2.1.0
     */
    public function toArray()
    {
        return [
            self::OPTION_BY_IP_AND_EMAIL => __('By IP and Email'),
            self::OPTION_BY_IP => __('By IP'),
            self::OPTION_BY_EMAIL => __('By Email'),
            self::OPTION_NONE => __('None'),
        ];
    }
}
