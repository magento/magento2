<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Block\Adminhtml\Online;

/**
 * Adminhtml customers online filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Filter extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->addField(
            'filter_value',
            'select',
            [
                'name' => 'filter_value',
                'onchange' => 'this.form.submit()',
                'values' => [
                    ['label' => __('All'), 'value' => ''],
                    ['label' => __('Customers Only'), 'value' => 'filterCustomers'],
                    ['label' => __('Visitors Only'), 'value' => 'filterGuests'],
                ],
                'no_span' => true
            ]
        );

        $form->setUseContainer(true);
        $form->setId('filter_form');
        $form->setMethod('post');

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
