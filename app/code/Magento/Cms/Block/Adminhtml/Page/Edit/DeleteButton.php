<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml\Page\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
//        $data = [];
//        if ($this->getPageId()) {
            $data = [
                'label' => __('Open Modal'),
                'class' => 'delete',
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [[
                                'targetName' => 'testform_form.testform_form.first.example_modal',
                                'actionName' => 'openModal'
                            ], [
                                'targetName' => 'testform_form.testform_form.first.example_modal.example',
                                'actionName' => 'updateData',
                                'params' => [
                                    'page_id' => 1
                                ]
                            ]]
                        ]
                    ]
                ],
                'on_click' => '',
                'sort_order' => 20,
            ];
//        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['page_id' => $this->getPageId()]);
    }
}
