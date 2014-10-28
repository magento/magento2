<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attribute add/edit form system tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;

class System extends Generic
{
    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('entity_attribute');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('System Properties')));

        if ($model->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', array('name' => 'attribute_id'));
        }

        $yesno = array(array('value' => 0, 'label' => __('No')), array('value' => 1, 'label' => __('Yes')));

        $fieldset->addField(
            'backend_type',
            'select',
            array(
                'name' => 'backend_type',
                'label' => __('Data Type for Saving in Database'),
                'title' => __('Data Type for Saving in Database'),
                'options' => array(
                    'text' => __('Text'),
                    'varchar' => __('Varchar'),
                    'static' => __('Static'),
                    'datetime' => __('Datetime'),
                    'decimal' => __('Decimal'),
                    'int' => __('Integer')
                )
            )
        );

        $fieldset->addField(
            'is_global',
            'select',
            array(
                'name' => 'is_global',
                'label' => __('Globally Editable'),
                'title' => __('Globally Editable'),
                'values' => $yesno
            )
        );

        $form->setValues($model->getData());

        if ($model->getAttributeId()) {
            $form->getElement('backend_type')->setDisabled(1);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
