<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Source;

use \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Class \Magento\Catalog\Model\Attribute\Source\Scopes
 *
 */
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
