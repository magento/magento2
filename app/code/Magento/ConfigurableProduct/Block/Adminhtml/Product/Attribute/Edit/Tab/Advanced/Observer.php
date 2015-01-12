<?php
/**
 * Product edit form observer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\Edit\Tab\Advanced;

use Magento\Backend\Model\Config\Source;

class Observer
{
    /**
     * @var \Magento\Backend\Model\Config\Source\Yesno
     */
    protected $optionList;

    /**
     * @param Source\Yesno $optionList
     */
    public function __construct(Source\Yesno $optionList)
    {
        $this->optionList = $optionList;
    }

    /**
     * @param \Magento\Framework\Event $event
     * @return void
     */
    public function observe($event)
    {
        /** @var \Magento\Framework\Data\Form\AbstractForm $form */
        $form = $event->getForm();
        /** @var  $fieldset */
        $fieldset = $form->getElement('advanced_fieldset');

        $fieldset->addField(
            'is_configurable',
            'select',
            [
                'name' => 'is_configurable',
                'label' => __('Use To Create Configurable Product'),
                'values' => $this->optionList->toOptionArray()
            ]
        );
    }
}
