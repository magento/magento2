<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Edit;

/**
 * Adminhtml store edit form
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
abstract class AbstractForm extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('coreStoreForm');
    }

    /**
     * Prepare form data
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $this->_prepareStoreFieldset($form);

        $form->addField(
            'store_type',
            'hidden',
            ['name' => 'store_type', 'no_span' => true, 'value' => $this->_coreRegistry->registry('store_type')]
        );

        $form->addField(
            'store_action',
            'hidden',
            [
                'name' => 'store_action',
                'no_span' => true,
                'value' => $this->_coreRegistry->registry('store_action')
            ]
        );

        $form->setAction($this->getUrl('adminhtml/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_store_edit_form_prepare_form', ['block' => $this]);

        return parent::_prepareForm();
    }

    /**
     * Build store type specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     * @abstract
     */
    abstract protected function _prepareStoreFieldset(\Magento\Framework\Data\Form $form);
}
