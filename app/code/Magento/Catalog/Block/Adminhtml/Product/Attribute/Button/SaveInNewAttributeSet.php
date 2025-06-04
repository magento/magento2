<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
