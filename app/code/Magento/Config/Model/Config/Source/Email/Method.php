<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Source for email send method
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Source\Email;

class Method implements \Magento\Framework\Option\ArrayInterface
{
    /**
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
