<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config\Source\Email;

/**
 * Source for email send method
 *
 * @api
 * @since 100.0.2
 */
class Method implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'bcc', 'label' => __('Bcc')],
            ['value' => 'copy', 'label' => __('Separate Email')],
        ];
        return $options;
    }
}
