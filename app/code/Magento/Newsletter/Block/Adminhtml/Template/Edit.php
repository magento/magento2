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
 * @category    Magento
 * @package     Magento_Newsletter
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter Template Edit Block
 *
 * @category   Magento
 * @package    Magento_Newsletter
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Template;

class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * Edit Block model
     *
     * @var bool
     */
    protected $_editMode = false;

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
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
     * Preparing block layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        // Load Wysiwyg on demand and Prepare layout
        $block = $this->getLayout()->getBlock('head');
        if ($this->_wysiwygConfig->isEnabled() && $block) {
            $block->setCanLoadTinyMce(true);
        }

        $this->addChild('back_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'     => __('Back'),
            'onclick'   => "window.location.href = '" . $this->getUrl('*/*') . "'",
            'class'     => 'action-back'
        ));

        $this->addChild('reset_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'     => __('Reset'),
            'onclick'   => 'window.location.href = window.location.href',
            'class'     => 'reset'
        ));

        $this->addChild('to_plain_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'     => __('Convert to Plain Text'),
            'onclick'   => 'templateControl.stripTags();',
            'id'            => 'convert_button',
            'class'     => 'convert'
        ));

        $this->addChild('to_html_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'     => __('Return HTML Version'),
            'onclick'   => 'templateControl.unStripTags();',
            'id'            => 'convert_button_back',
            'style'     => 'display:none',
            'class'     => 'return'
        ));

        $this->addChild('save_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'     => __('Save Template'),
            'onclick'   => 'templateControl.save();',
            'class'     => 'save primary'
        ));

        $this->addChild('save_as_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'     => __('Save As'),
            'onclick'   => 'templateControl.saveAs();',
            'class'     => 'save-as'
        ));

        $this->addChild('preview_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'     => __('Preview Template'),
            'onclick'   => 'templateControl.preview();',
            'class'     => 'preview'
        ));

        $this->addChild('delete_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'     => __('Delete Template'),
            'onclick'   => 'templateControl.deleteTemplate();',
            'class'     => 'delete'
        ));

        return parent::_prepareLayout();
    }

    /**
     * Retrieve Back Button HTML
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Retrieve Reset Button HTML
     *
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve Convert To Plain Button HTML
     *
     * @return string
     */
    public function getToPlainButtonHtml()
    {
        return $this->getChildHtml('to_plain_button');
    }

    /**
     * Retrieve Convert to HTML Button HTML
     *
     * @return string
     */
    public function getToHtmlButtonHtml()
    {
        return $this->getChildHtml('to_html_button');
    }

    /**
     * Retrieve Save Button HTML
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Retrieve Preview Button HTML
     *
     * @return string
     */
    public function getPreviewButtonHtml()
    {
        return $this->getChildHtml('preview_button');
    }

    /**
     * Retrieve Delete Button HTML
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * Retrieve Save as Button HTML
     *
     * @return string
     */
    public function getSaveAsButtonHtml()
    {
        return $this->getChildHtml('save_as_button');
    }

    /**
     * Set edit flag for block
     *
     * @param boolean $value
     * @return $this
     */
    public function setEditMode($value = true)
    {
        $this->_editMode = (bool)$value;
        return $this;
    }

    /**
     * Return edit flag for block
     *
     * @return boolean
     */
    public function getEditMode()
    {
        return $this->_editMode;
    }

    /**
     * Return header text for form
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getEditMode()) {
            return __('Edit Newsletter Template');
        }

        return  __('New Newsletter Template');
    }

    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getForm()
    {
        return $this->getLayout()
            ->createBlock('Magento\Newsletter\Block\Adminhtml\Template\Edit\Form')
            ->toHtml();
    }

    /**
     * Return return template name for JS
     *
     * @return string
     */
    public function getJsTemplateName()
    {
        return addcslashes($this->getModel()->getTemplateCode(), "\"\r\n\\");
    }

    /**
     * Return action url for form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Return preview action url for form
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->getUrl('*/*/preview');
    }

    /**
     * Check Template Type is Plain Text
     *
     * @return bool
     */
    public function isTextType()
    {
        return $this->getModel()->isPlain();
    }

    /**
     * Return delete url for customer group
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('id' => $this->getRequest()->getParam('id')));
    }

    /**
     * Retrieve Save As Flag
     *
     * @return int
     */
    public function getSaveAsFlag()
    {
        return $this->getRequest()->getParam('_save_as_flag') ? '1' : '';
    }

    /**
     * Getter for single store mode check
     *
     * @return boolean
     */
    protected function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Getter for id of current store (the only one in single-store mode and current in multi-stores mode)
     *
     * @return boolean
     */
    protected function getStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();
    }
}
