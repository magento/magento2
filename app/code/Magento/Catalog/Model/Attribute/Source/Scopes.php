<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Source;

use \Magento\Catalog\Model\Resource\Eav\Attribute;

class Scopes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Attribute::SCOPE_STORE,
                'label' => __('Store View'),
            ],
            [
                'value' => Attribute::SCOPE_WEBSITE,
                'label' => __('Web Site'),
            ],
            [
                'value' => Attribute::SCOPE_GLOBAL,
                'label' => __('Global'),
            ],
        ];
    }
}
