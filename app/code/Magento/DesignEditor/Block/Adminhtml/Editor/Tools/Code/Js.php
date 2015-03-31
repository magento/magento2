<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code;

/**
 * Block that renders JS tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Js extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $_customizationConfig;

    /**
     * @var \Magento\DesignEditor\Model\Theme\Context
     */
    protected $_themeContext;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Theme\Model\Config\Customization $customizationConfig
     * @param \Magento\DesignEditor\Model\Theme\Context $themeContext
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Theme\Model\Config\Customization $customizationConfig,
        \Magento\DesignEditor\Model\Theme\Context $themeContext,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_customizationConfig = $customizationConfig;
        $this->_themeContext = $themeContext;
    }

    /**
     * Create a form element with necessary controls
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['action' => '#', 'method' => 'post']]);
        $this->setForm($form);
        $form->setUseContainer(true);

        $form->addType('js_files', 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Uploader');

        $jsConfig = [
            'name' => 'js_files_uploader',
            'title' => __('Select JS Files to Upload'),
            'accept' => 'application/x-javascript',
            'multiple' => '1',
        ];
        if ($this->_customizationConfig->isThemeAssignedToStore($this->_themeContext->getEditableTheme())) {
            $confirmMessage = __(
                'These JavaScript files may change the appearance of your live store(s).' .
                ' Are you sure you want to do this?'
            );
            $jsConfig['onclick'] = "return confirm('{$confirmMessage}');";
        }
        $form->addField('js_files_uploader', 'js_files', $jsConfig);

        parent::_prepareForm();
        return $this;
    }

    /**
     * Return confirmation message for delete action
     *
     * @return \Magento\Framework\Phrase
     */
    public function getConfirmMessageDelete()
    {
        return __(
            'Are you sure you want to delete this JavaScript file?' .
            ' The changes to your theme will not be reversible.'
        );
    }

    /**
     * Get upload js url
     *
     * @return string
     */
    public function getJsUploadUrl()
    {
        return $this->getUrl(
            'adminhtml/system_design_editor_tools/uploadjs',
            ['theme_id' => $this->_themeContext->getEditableTheme()->getId()]
        );
    }

    /**
     * Get reorder js url
     *
     * @return string
     */
    public function getJsReorderUrl()
    {
        return $this->getUrl(
            'adminhtml/system_design_editor_tools/reorderjs',
            ['theme_id' => $this->_themeContext->getEditableTheme()->getId()]
        );
    }

    /**
     * Get delete js url
     *
     * @return string
     */
    public function getJsDeleteUrl()
    {
        return $this->getUrl(
            'adminhtml/system_design_editor_tools/deleteCustomFiles',
            ['theme_id' => $this->_themeContext->getEditableTheme()->getId()]
        );
    }

    /**
     * Get custom js files
     *
     * @return \Magento\Theme\Model\Resource\Theme\File\Collection
     */
    public function getFiles()
    {
        $customization = $this->_themeContext->getStagingTheme()->getCustomization();
        $jsFiles = $customization->getFilesByType(\Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE);
        return $this->jsonHelper->jsonEncode($customization->generateFileInfo($jsFiles));
    }

    /**
     * Get js tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTitle()
    {
        return __('Custom javascript files');
    }
}
