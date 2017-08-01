<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Source for email send method
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Source\Email;

/**
 * @api
 * @since 2.0.0
 */
class Method implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
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
