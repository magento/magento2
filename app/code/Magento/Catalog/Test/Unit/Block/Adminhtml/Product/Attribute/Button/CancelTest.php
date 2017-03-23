<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute\Button;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Button\Cancel;

/**
 * Class CancelTest
 */
class CancelTest extends GenericTest
{
    /**
     * {@inheritdoc}
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(Cancel::class, [
            'context' => $this->contextMock,
            'registry' => $this->registryMock,
        ]);
    }

    public function testGetButtonData()
    {
        $this->assertEquals(
            [
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
            ],
            $this->getModel()->getButtonData()
        );
    }
}
