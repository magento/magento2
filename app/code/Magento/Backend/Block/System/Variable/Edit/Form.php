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
 * Custom Variable Edit Form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\System\Variable\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Getter
     *
     * @return \Magento\Core\Model\Variable
     */
    public function getVariable()
    {
        return $this->_coreRegistry->registry('current_variable');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return \Magento\Backend\Block\System\Variable\Edit\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'))
        );

        $fieldset = $form->addFieldset('base', array('legend' => __('Variable'), 'class' => 'fieldset-wide'));

        $fieldset->addField(
            'code',
            'text',
            array(
                'name' => 'code',
                'label' => __('Variable Code'),
                'title' => __('Variable Code'),
                'required' => true,
                'class' => 'validate-xml-identifier'
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array('name' => 'name', 'label' => __('Variable Name'), 'title' => __('Variable Name'), 'required' => true)
        );

        $useDefault = false;
        if ($this->getVariable()->getId() && $this->getVariable()->getStoreId()) {
            $useDefault = !(bool)$this->getVariable()->getStoreHtmlValue();
            $this->getVariable()->setUseDefaultValue((int)$useDefault);
            $fieldset->addField(
                'use_default_value',
                'select',
                array(
                    'name' => 'use_default_value',
                    'label' => __('Use Default Variable Values'),
                    'title' => __('Use Default Variable Values'),
                    'onchange' => 'toggleValueElement(this);',
                    'values' => array(0 => __('No'), 1 => __('Yes'))
                )
            );
        }

        $fieldset->addField(
            'html_value',
            'textarea',
            array(
                'name' => 'html_value',
                'label' => __('Variable HTML Value'),
                'title' => __('Variable HTML Value'),
                'disabled' => $useDefault
            )
        );

        $fieldset->addField(
            'plain_value',
            'textarea',
            array(
                'name' => 'plain_value',
                'label' => __('Variable Plain Value'),
                'title' => __('Variable Plain Value'),
                'disabled' => $useDefault
            )
        );

        $form->setValues($this->getVariable()->getData())->addFieldNameSuffix('variable')->setUseContainer(true);

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
