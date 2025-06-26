<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml system template edit form
 */
namespace Magento\Email\Block\Adminhtml\Template\Edit;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Adminhtml email template edit form block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Variable\Model\Source\Variables
     */
    protected $_variables;

    /**
     * @var \Magento\Variable\Model\VariableFactory
     */
    protected $_variableFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Variable\Model\VariableFactory $variableFactory
     * @param \Magento\Variable\Model\Source\Variables $variables
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param SecureHtmlRenderer|null $secureRenderer
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Variable\Model\VariableFactory $variableFactory,
        \Magento\Variable\Model\Source\Variables $variables,
        array $data = [],
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->_variableFactory = $variableFactory;
        $this->_variables = $variables;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare layout.
     *
     * Add files to use dialog windows.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->addPageAsset('prototype/windows/themes/default.css');
        return parent::_prepareLayout();
    }

    /**
     * Add fields to form and create template info form
     *
     * @return \Magento\Backend\Block\Widget\Form
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Template Information'), 'class' => 'fieldset-wide']
        );

        $templateId = $this->getEmailTemplate()->getId();
        $fieldset->addField(
            'currently_used_for',
            'label',
            [
                'label' => __('Currently Used For'),
                'container_id' => 'currently_used_for',
                'after_element_html' => $this->secureRenderer->renderTag(
                    'script',
                    [],
                    'require(["prototype"], function () {' .
                    (!$this->getEmailTemplate()->getSystemConfigPathsWhereCurrentlyUsed() ? '$(\'' .
                    'currently_used_for' .
                    '\').hide(); ' : '') .
                    '});',
                    false
                ),
            ]
        );

        $fieldset->addField(
            'template_code',
            'text',
            ['name' => 'template_code', 'label' => __('Template Name'), 'required' => true]
        );
        $fieldset->addField(
            'template_subject',
            'text',
            ['name' => 'template_subject', 'label' => __('Template Subject'), 'required' => true]
        );
        $fieldset->addField('orig_template_variables', 'hidden', ['name' => 'orig_template_variables']);
        $fieldset->addField(
            'variables',
            'hidden',
            ['name' => 'variables', 'value' => $this->serializer->serialize($this->getVariables())]
        );
        $fieldset->addField('template_variables', 'hidden', ['name' => 'template_variables']);

        $insertVariableButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class,
            '',
            [
                'data' => [
                    'type' => 'button',
                    'label' => __('Insert Variable...'),
                    'onclick' => 'templateControl.openVariableChooser();return false;',
                ]
            ]
        );

        $fieldset->addField('insert_variable', 'note', ['text' => $insertVariableButton->toHtml(), 'label' => '']);

        $fieldset->addField(
            'template_text',
            'textarea',
            [
                'name' => 'template_text',
                'label' => __('Template Content'),
                'title' => __('Template Content'),
                'required' => true,
                'style' => 'height:24em;'
            ]
        );

        if (!$this->getEmailTemplate()->isPlain()) {
            $fieldset->addField(
                'template_styles',
                'textarea',
                [
                    'name' => 'template_styles',
                    'label' => __('Template Styles'),
                    'container_id' => 'field_template_styles'
                ]
            );
        }

        if ($templateId) {
            $form->addValues($this->getEmailTemplate()->getData());
        }

        $values = $this->_backendSession->getData('email_template_form_data', true);
        if ($values) {
            $form->setValues($values);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return current email template model
     *
     * @return \Magento\Email\Model\Template
     */
    public function getEmailTemplate()
    {
        return $this->getData('email_template');
    }

    /**
     * Retrieve variables to insert into email
     *
     * @return array
     */
    public function getVariables()
    {
        $variables = $this->_variables->toOptionArray(true);
        $customVariables = $this->_variableFactory->create()->getVariablesOptionArray(true);
        if ($customVariables) {
            $variables = array_merge_recursive($variables, $customVariables);
        }
        $template = $this->getEmailTemplate();
        if ($template->getId() && ($templateVariables = $template->getVariablesOptionArray(true))) {
            $variables = array_merge_recursive($variables, [$templateVariables]);
        }
        return $variables;
    }
}
