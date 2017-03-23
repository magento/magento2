<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Custom Variable Edit Form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Variable\Block\System\Variable\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Getter
     *
     * @return \Magento\Variable\Model\Variable
     */
    public function getVariable()
    {
        return $this->_coreRegistry->registry('current_variable');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return \Magento\Variable\Block\System\Variable\Edit\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base', ['legend' => __('Variable'), 'class' => 'fieldset-wide']);

        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'label' => __('Variable Code'),
                'title' => __('Variable Code'),
                'required' => true,
                'class' => 'validate-xml-identifier'
            ]
        );

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Variable Name'), 'title' => __('Variable Name'), 'required' => true]
        );

        $useDefault = false;
        if ($this->getVariable()->getId() && $this->getVariable()->getStoreId()) {
            $useDefault = !(bool)$this->getVariable()->getStoreHtmlValue();
            $this->getVariable()->setUseDefaultValue((int)$useDefault);
            $fieldset->addField(
                'use_default_value',
                'select',
                [
                    'name' => 'use_default_value',
                    'label' => __('Use Default Variable Values'),
                    'title' => __('Use Default Variable Values'),
                    'onchange' => 'toggleValueElement(this);',
                    'values' => [0 => __('No'), 1 => __('Yes')]
                ]
            );
        }

        $fieldset->addField(
            'html_value',
            'textarea',
            [
                'name' => 'html_value',
                'label' => __('Variable HTML Value'),
                'title' => __('Variable HTML Value'),
                'disabled' => $useDefault
            ]
        );

        $fieldset->addField(
            'plain_value',
            'textarea',
            [
                'name' => 'plain_value',
                'label' => __('Variable Plain Value'),
                'title' => __('Variable Plain Value'),
                'disabled' => $useDefault
            ]
        );

        $form->setValues($this->getVariable()->getData())->addFieldNameSuffix('variable')->setUseContainer(true);

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
