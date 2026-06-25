<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Attribute\Source;

use \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class Scopes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => ScopedAttributeInterface::SCOPE_STORE,
                'label' => __('Store View'),
            ],
            [
                'value' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'label' => __('Web Site'),
            ],
            [
                'value' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'label' => __('Global'),
            ],
        ];
    }
}
