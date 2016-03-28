<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Config\Model\Config\Source\Dev;

class Dbautoup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\Framework\App\ResourceConnection::AUTO_UPDATE_ALWAYS, 'label' => __('Always (during development)')],
            ['value' => \Magento\Framework\App\ResourceConnection::AUTO_UPDATE_ONCE, 'label' => __('Only Once (version upgrade)')],
            ['value' => \Magento\Framework\App\ResourceConnection::AUTO_UPDATE_NEVER, 'label' => __('Never (production)')]
        ];
    }
}
