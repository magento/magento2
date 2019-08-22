<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Button;

/**
 * Class Cancel
 */
class Cancel extends Generic
{
    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Cancel'),
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'product_form.product_form.add_attribute_modal'
                                    . '.create_new_attribute_modal',
                                'actionName' => 'toggleModal'
                            ]
                        ]
                    ]
                ]
            ],
            'on_click' => ''
        ];
    }
}
