<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\System\Config\Source;

/**
 * Class \Magento\Catalog\Model\System\Config\Source\Inputtype
 *
 */
class Inputtype
{
    /**
     * Get input types which use predefined source
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'multiselect', 'label' => __('Multiple Select')],
            ['value' => 'select', 'label' => __('Dropdown')]
        ];
    }
}
