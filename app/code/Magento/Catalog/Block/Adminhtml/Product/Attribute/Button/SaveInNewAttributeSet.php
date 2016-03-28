<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Button;

class SaveInNewAttributeSet extends Generic
{
    /**
     * Get button data
     *
     * @return array
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
