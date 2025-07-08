<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab;

/**
 * Theme form, Js editor tab
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Js extends \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab
{
    /**
     * Create a form element with necessary controls
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $this->setForm($form);
        $this->_addThemeJsFieldset();
        parent::_prepareForm();
        return $this;
    }

    /**
     * Set theme js fieldset
     *
     * @return $this
     */
    protected function _addThemeJsFieldset()
    {
        $form = $this->getForm();
        $themeFieldset = $form->addFieldset('theme_js', ['legend' => __('Theme JavaScript')]);
        $customization = $this->_getCurrentTheme()->getCustomization();
        $customJsFiles = $customization->getFilesByType(
            \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE
        );

        /** @var $jsFieldsetRenderer \Magento\Backend\Block\Widget\Form\Renderer\Fieldset */
        $jsFieldsetRenderer = $this->getChildBlock('theme_edit_tabs_tab_js_tab_content');
        $jsFieldsetRenderer->setJsFiles($customization->generateFileInfo($customJsFiles));

        $jsFieldset = $themeFieldset->addFieldset('js_fieldset_javascript_content', ['class' => 'fieldset-wide']);

        $this->_addElementTypes($themeFieldset);

        $themeFieldset->addField(
            'js_files_uploader',
            'button',
            [
                'name' => 'js_files_uploader',
                'label' => __('Select JS Files to Upload'),
                'title' => __('Select JS Files to Upload'),
                'accept' => 'application/x-javascript',
                'multiple' => '',
                'value' => __('Browse JS Files'),
                'note' => $this->_getUploadJsFileNote()
            ]
        );

        $themeFieldset->addField(
            'js_uploader_button',
            'button',
            ['name' => 'js_uploader_button', 'value' => __('Upload JS Files'), 'disabled' => 'disabled']
        );

        $jsFieldset->setRenderer($jsFieldsetRenderer);
        return $this;
    }

    /**
     * Set additional form field type
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $fileElement = \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File::class;
        return ['js_files' => $fileElement];
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('JS Editor');
    }

    /**
     * Get upload js url
     *
     * @return string
     */
    public function getJsUploadUrl()
    {
        return $this->getUrl(
            'adminhtml/system_design_theme/uploadjs',
            ['id' => $this->_getCurrentTheme()->getId()]
        );
    }

    /**
     * Get note string for js file to Upload
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getUploadJsFileNote()
    {
        return __('Allowed file types *.js.');
    }
}
