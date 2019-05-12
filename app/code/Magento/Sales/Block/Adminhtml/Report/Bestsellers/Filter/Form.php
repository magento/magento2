<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Report\Bestsellers\Filter;

/**
 * Sales Adminhtml report bestseller filter form
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.1.0
 */
class Form extends \Magento\Sales\Block\Adminhtml\Report\Filter\Form
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
            $fieldset->addField(
                'rating_limit',
                'select',
                [
                    'name' => 'rating_limit',
                    'label' => __('Display items'),
                    'options' => array_combine($i = [5, 10, 20, 50, 100], $i),
                ],
                'to'
            );
        }

        return $this;
    }
}
