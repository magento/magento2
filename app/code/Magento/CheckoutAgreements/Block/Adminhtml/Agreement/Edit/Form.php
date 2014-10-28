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
namespace Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init class
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('checkoutAgreementForm');
        $this->setTitle(__('Terms and Conditions Information'));
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('checkout_agreement');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'))
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend' => __('Terms and Conditions Information'), 'class' => 'fieldset-wide')
        );

        if ($model->getId()) {
            $fieldset->addField('agreement_id', 'hidden', array('name' => 'agreement_id'));
        }
        $fieldset->addField(
            'name',
            'text',
            array(
                'name' => 'name',
                'label' => __('Condition Name'),
                'title' => __('Condition Name'),
                'required' => true
            )
        );

        $fieldset->addField(
            'is_active',
            'select',
            array(
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => array('1' => __('Enabled'), '0' => __('Disabled'))
            )
        );

        $fieldset->addField(
            'is_html',
            'select',
            array(
                'label' => __('Show Content as'),
                'title' => __('Show Content as'),
                'name' => 'is_html',
                'required' => true,
                'options' => array(0 => __('Text'), 1 => __('HTML'))
            )
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                array(
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true)
                )
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                array('name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId())
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'checkbox_text',
            'editor',
            array(
                'name' => 'checkbox_text',
                'label' => __('Checkbox Text'),
                'title' => __('Checkbox Text'),
                'rows' => '5',
                'cols' => '30',
                'wysiwyg' => false,
                'required' => true
            )
        );

        $fieldset->addField(
            'content',
            'editor',
            array(
                'name' => 'content',
                'label' => __('Content'),
                'title' => __('Content'),
                'style' => 'height:24em;',
                'wysiwyg' => false,
                'required' => true
            )
        );

        $fieldset->addField(
            'content_height',
            'text',
            array(
                'name' => 'content_height',
                'label' => __('Content Height (css)'),
                'title' => __('Content Height'),
                'maxlength' => 25,
                'class' => 'validate-css-length'
            )
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
