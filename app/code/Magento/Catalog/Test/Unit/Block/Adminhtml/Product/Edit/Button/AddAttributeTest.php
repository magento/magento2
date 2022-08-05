<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\AddAttribute;

class AddAttributeTest extends GenericTest
{
    private const STUB_EXPECTED_CONFIG = [
        'label' => 'Add Attribute',
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

    public function testGetButtonData()
    {
        $this->assertEquals(
            self::STUB_EXPECTED_CONFIG,
            $this->getModel(AddAttribute::class)->getButtonData()
        );
    }
}
