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
        array $data = array()
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
            array('data' => array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'))
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend' => __('Template Information'), 'class' => 'fieldset-wide')
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id', 'value' => $model->getId()));
        }

        $fieldset->addField(
            'code',
            'text',
            array(
                'name' => 'code',
                'label' => __('Template Name'),
                'title' => __('Template Name'),
                'required' => true,
                'value' => $model->getTemplateCode()
            )
        );

        $fieldset->addField(
            'subject',
            'text',
            array(
                'name' => 'subject',
                'label' => __('Template Subject'),
                'title' => __('Template Subject'),
                'required' => true,
                'value' => $model->getTemplateSubject()
            )
        );

        $fieldset->addField(
            'sender_name',
            'text',
            array(
                'name' => 'sender_name',
                'label' => __('Sender Name'),
                'title' => __('Sender Name'),
                'required' => true,
                'value' => $model->getId() !== null ? $model->getTemplateSenderName() : $identityName
            )
        );

        $fieldset->addField(
            'sender_email',
            'text',
            array(
                'name' => 'sender_email',
                'label' => __('Sender Email'),
                'title' => __('Sender Email'),
                'class' => 'validate-email',
                'required' => true,
                'value' => $model->getId() !== null ? $model->getTemplateSenderEmail() : $identityEmail
            )
        );


        $widgetFilters = array('is_email_compatible' => 1);
        $wysiwygConfig = $this->_wysiwygConfig->getConfig(array('widget_filters' => $widgetFilters));
        if ($model->isPlain()) {
            $wysiwygConfig->setEnabled(false);
        }
        $fieldset->addField(
            'text',
            'editor',
            array(
                'name' => 'text',
                'label' => __('Template Content'),
                'title' => __('Template Content'),
                'required' => true,
                'state' => 'html',
                'style' => 'height:36em;',
                'value' => $model->getTemplateText(),
                'config' => $wysiwygConfig
            )
        );

        if (!$model->isPlain()) {
            $fieldset->addField(
                'template_styles',
                'textarea',
                array(
                    'name' => 'styles',
                    'label' => __('Template Styles'),
                    'container_id' => 'field_template_styles',
                    'value' => $model->getTemplateStyles()
                )
            );
        }

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
