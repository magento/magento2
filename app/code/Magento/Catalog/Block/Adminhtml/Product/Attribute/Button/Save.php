<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Button;


class Save extends Generic
{
    public function getButtonData()
    {
        return [
            'label' => __('Save Attribute'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'product_attribute_add_form.product_attribute_add_form',
                                'actionName' => 'save'
                            ],
                        ]
                    ]
                ]
            ],
            'on_click' => ''
        ];
    }
}