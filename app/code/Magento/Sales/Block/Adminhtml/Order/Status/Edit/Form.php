<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Status\Edit;

/**
 * Edit status form
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Status\NewStatus\Form
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('new_order_status');
    }

    /**
     * Modify structure of new status form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $form->getElement('base_fieldset')->removeField('is_new');
        $form->getElement('base_fieldset')->removeField('status');
        $form->setAction(
            $this->getUrl('sales/order_status/save', ['status' => $this->getRequest()->getParam('status')])
        );
        return $this;
    }
}
