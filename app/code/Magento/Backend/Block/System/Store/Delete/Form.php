<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Delete;

/**
 * Adminhtml cms block edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('store_delete_form');
        $this->setTitle(__('Block Information'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $dataObject = $this->getDataObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('store_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Backup Options'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField('item_id', 'hidden', ['name' => 'item_id', 'value' => $dataObject->getId()]);

        $fieldset->addField(
            'create_backup',
            'select',
            [
                'label' => __('Create DB Backup'),
                'title' => __('Create DB Backup'),
                'name' => 'create_backup',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'value' => '1'
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
