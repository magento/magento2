<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Button;

/**
 * Class AddAttribute
 * @since 2.1.0
 */
class AddAttribute extends Generic
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getButtonData()
    {
        return [
            'label' => __('Add Attribute'),
            'class' => 'action-secondary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'product_form.product_form.add_attribute_modal',
                                'actionName' => 'toggleModal'
                            ],
                            [
                                'targetName' => 'product_form.product_form.add_attribute_modal.product_attributes_grid',
                                'actionName' => 'render'
                            ]
                        ]
                    ]
                ]
            ],
            'on_click' => '',
            'sort_order' => 20
        ];
    }
}
