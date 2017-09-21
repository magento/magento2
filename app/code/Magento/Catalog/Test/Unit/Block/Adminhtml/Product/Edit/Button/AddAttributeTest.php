<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\AddAttribute;

/**
 * Class AddAttributeTest
 */
class AddAttributeTest extends GenericTest
{
    public function testGetButtonData()
    {
        $this->assertEquals(
            [
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
                                    'targetName' =>
                                        'product_form.product_form.add_attribute_modal.product_attributes_grid',
                                    'actionName' => 'render'
                                ]
                            ]
                        ]
                    ]
                ],
                'on_click' => '',
                'sort_order' => 20
            ],
            $this->getModel(AddAttribute::class)->getButtonData()
        );
    }
}
