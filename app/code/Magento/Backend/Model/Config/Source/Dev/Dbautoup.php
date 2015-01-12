<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Source\Dev;

class Dbautoup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\Framework\App\Resource::AUTO_UPDATE_ALWAYS, 'label' => __('Always (during development)')],
            ['value' => \Magento\Framework\App\Resource::AUTO_UPDATE_ONCE, 'label' => __('Only Once (version upgrade)')],
            ['value' => \Magento\Framework\App\Resource::AUTO_UPDATE_NEVER, 'label' => __('Never (production)')]
        ];
    }
}
