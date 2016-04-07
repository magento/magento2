<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter Template Edit Form Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Template\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Retrieve template object
     *
     * @return \Magento\Newsletter\Model\Template
     */
    public function getModel()
    {
        return $this->_coreRegistry->registry('_current_template');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->getModel();
        $identity = $this->_scopeConfig->getValue(
            \Magento\Newsletter\Model\Subscriber::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $identityName = $this->_scopeConfig->getValue(
            'trans_email/ident_' . $identity . '/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $identityEmail = $this->_scopeConfig->getValue(
            'trans_email/ident_' . $identity . '/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Template Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id', 'value' => $model->getId()]);
        }

        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'label' => __('Template Name'),
                'title' => __('Template Name'),
                'required' => true,
                'value' => $model->getTemplateCode()
            ]
        );

        $fieldset->addField(
            'subject',
            'text',
            [
                'name' => 'subject',
                'label' => __('Template Subject'),
                'title' => __('Template Subject'),
                'required' => true,
                'value' => $model->getTemplateSubject()
            ]
        );

        $fieldset->addField(
            'sender_name',
            'text',
            [
                'name' => 'sender_name',
                'label' => __('Sender Name'),
                'title' => __('Sender Name'),
                'required' => true,
                'value' => $model->getId() !== null ? $model->getTemplateSenderName() : $identityName
            ]
        );

        $fieldset->addField(
            'sender_email',
            'text',
            [
                'name' => 'sender_email',
                'label' => __('Sender Email'),
                'title' => __('Sender Email'),
                'class' => 'validate-email',
                'required' => true,
                'value' => $model->getId() !== null ? $model->getTemplateSenderEmail() : $identityEmail
            ]
        );

        $widgetFilters = ['is_email_compatible' => 1];
        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['widget_filters' => $widgetFilters]);
        if ($model->isPlain()) {
            $wysiwygConfig->setEnabled(false);
        }
        $fieldset->addField(
            'text',
            'editor',
            [
                'name' => 'text',
                'label' => __('Template Content'),
                'title' => __('Template Content'),
                'required' => true,
                'state' => 'html',
                'style' => 'height:36em;',
                'value' => $model->getTemplateText(),
                'config' => $wysiwygConfig
            ]
        );

        if (!$model->isPlain()) {
            $fieldset->addField(
                'template_styles',
                'textarea',
                [
                    'name' => 'styles',
                    'label' => __('Template Styles'),
                    'container_id' => 'field_template_styles',
                    'value' => $model->getTemplateStyles()
                ]
            );
        }

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
