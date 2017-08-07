<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Button;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Attribute\Button\SaveInNewAttributeSet
 *
 * @since 2.1.0
 */
class SaveInNewAttributeSet extends Generic
{
    /**
     * Get button data
     *
     * @return array
     * @since 2.1.0
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save in New Attribute Set'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'product_attribute_add_form.product_attribute_add_form',
                                'actionName' => 'saveAttributeInNewSet'
                            ],
                        ]
                    ]
                ]
            ],
            'on_click' => ''
        ];
    }
}
