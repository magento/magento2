<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product attribute add/edit form system tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\System
 *
 * @since 2.0.0
 */
class System extends Generic
{
    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('entity_attribute');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('System Properties')]);

        if ($model->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'attribute_id']);
        }

        $yesno = [['value' => 0, 'label' => __('No')], ['value' => 1, 'label' => __('Yes')]];

        $fieldset->addField(
            'backend_type',
            'select',
            [
                'name' => 'backend_type',
                'label' => __('Data Type for Saving in Database'),
                'title' => __('Data Type for Saving in Database'),
                'options' => [
                    'text' => __('Text'),
                    'varchar' => __('Varchar'),
                    'static' => __('Static'),
                    'datetime' => __('Datetime'),
                    'decimal' => __('Decimal'),
                    'int' => __('Integer'),
                ]
            ]
        );

        $fieldset->addField(
            'is_global',
            'select',
            [
                'name' => 'is_global',
                'label' => __('Globally Editable'),
                'title' => __('Globally Editable'),
                'values' => $yesno
            ]
        );

        $form->setValues($model->getData());

        if ($model->getAttributeId()) {
            $form->getElement('backend_type')->setDisabled(1);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
