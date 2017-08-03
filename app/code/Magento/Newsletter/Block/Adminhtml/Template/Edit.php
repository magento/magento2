<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Newsletter Template Edit Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Template;

use Magento\Backend\Block\Widget;
use Magento\Framework\App\TemplateTypesInterface;

/**
 * @api
 * @since 2.0.0
 */
class Edit extends Widget
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     * @since 2.0.0
     */
    protected $_wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve template object
     *
     * @return \Magento\Newsletter\Model\Template
     * @since 2.0.0
     */
    public function getModel()
    {
        return $this->_coreRegistry->registry('_current_template');
    }

    /**
     * Preparing block layout
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        // Load Wysiwyg on demand and Prepare layout
//        $block = $this->getLayout()->getBlock('head');
//        if ($this->_wysiwygConfig->isEnabled() && $block) {
//            $block->setCanLoadTinyMce(true);
//        }

        $this->getToolbar()->addChild(
            'back_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Back'),
                'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                'class' => 'action-back'
            ]
        );

        $this->getToolbar()->addChild(
            'reset_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Reset'),
                'onclick' => 'window.location.href = window.location.href',
                'class' => 'reset'
            ]
        );

        if (!$this->isTextType()) {
            $this->getToolbar()->addChild(
                'to_plain_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Convert to Plain Text'),
                    'data_attribute' => [
                        'role' => 'template-strip',
                    ],
                    'id' => 'convert_button',
                    'class' => 'convert'
                ]
            );

            $this->getToolbar()->addChild(
                'to_html_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Return HTML Version'),
                    'data_attribute' => [
                        'role' => 'template-unstrip',
                    ],
                    'id' => 'convert_button_back',
                    'style' => 'display:none',
                    'class' => 'return'
                ]
            );
        }

        $this->getToolbar()->addChild(
            'preview_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Preview Template'),
                'data_attribute' => [
                    'role' => 'template-preview',
                ],
                'class' => 'preview'
            ]
        );

        if ($this->getEditMode()) {
            $this->getToolbar()->addChild(
                'delete_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Delete Template'),
                    'data_attribute' => [
                        'role' => 'template-delete',
                    ],
                    'class' => 'delete'
                ]
            );

            $this->getToolbar()->addChild(
                'save_as_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Save As'),
                    'data_attribute' => [
                        'role' => 'template-save-as',
                    ],
                    'class' => 'save-as'
                ]
            );
        }

        $this->getToolbar()->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Save Template'),
                'data_attribute' => [
                    'role' => 'template-save',
                ],
                'class' => 'save primary'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Return edit flag for block
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getEditMode()
    {
        if ($this->getModel()->getId()) {
            return true;
        }
        return false;
    }

    /**
     * Return header text for form
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        if ($this->getEditMode()) {
            return __('Edit Newsletter Template');
        }

        return __('New Newsletter Template');
    }

    /**
     * Return form block HTML
     *
     * @return string
     * @since 2.0.0
     */
    public function getForm()
    {
        return $this->getLayout()->createBlock(
             \Magento\Newsletter\Block\Adminhtml\Template\Edit\Form::class
        )->toHtml();
    }

    /**
     * Return return template name for JS
     *
     * @return string
     * @since 2.0.0
     */
    public function getJsTemplateName()
    {
        return addcslashes($this->getModel()->getTemplateCode(), "\"\r\n\\");
    }

    /**
     * Return action url for form
     *
     * @return string
     * @since 2.0.0
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Return preview action url for form
     *
     * @return string
     * @since 2.0.0
     */
    public function getPreviewUrl()
    {
        return $this->getUrl('*/*/preview', ['id' => $this->getRequest()->getParam('id')]);
    }

    /**
     * Check Template Type is Plain Text
     *
     * @return bool
     * @since 2.0.0
     */
    public function isTextType()
    {
        return $this->getModel()->isPlain();
    }

    /**
     * Return template type from template object or TYPE_HTML by default
     *
     * @return int
     * @since 2.0.0
     */
    public function getTemplateType()
    {
        if ($this->getModel()->getTemplateType()) {
            return $this->getModel()->getTemplateType();
        }
        return TemplateTypesInterface::TYPE_HTML;
    }

    /**
     * Return delete url for customer group
     *
     * @return string
     * @since 2.0.0
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getRequest()->getParam('id')]);
    }

    /**
     * Retrieve Save As Flag
     *
     * @return int
     * @since 2.0.0
     */
    public function getSaveAsFlag()
    {
        return $this->getRequest()->getParam('_save_as_flag') ? '1' : '';
    }

    /**
     * Getter for single store mode check
     *
     * @return boolean
     * @since 2.0.0
     */
    protected function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Getter for id of current store (the only one in single-store mode and current in multi-stores mode)
     *
     * @return int
     * @since 2.0.0
     */
    protected function getStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();
    }
}
