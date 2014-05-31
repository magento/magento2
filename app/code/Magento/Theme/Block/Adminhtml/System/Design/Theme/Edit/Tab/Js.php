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
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab;

/**
 * Theme form, Js editor tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
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
        $themeFieldset = $form->addFieldset('theme_js', array('legend' => __('Theme JavaScript')));
        $customization = $this->_getCurrentTheme()->getCustomization();
        $customJsFiles = $customization->getFilesByType(
            \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE
        );

        /** @var $jsFieldsetRenderer \Magento\Backend\Block\Widget\Form\Renderer\Fieldset */
        $jsFieldsetRenderer = $this->getChildBlock('theme_edit_tabs_tab_js_tab_content');
        $jsFieldsetRenderer->setJsFiles($customization->generateFileInfo($customJsFiles));

        $jsFieldset = $themeFieldset->addFieldset('js_fieldset_javascript_content', array('class' => 'fieldset-wide'));

        $this->_addElementTypes($themeFieldset);

        $themeFieldset->addField(
            'js_files_uploader',
            'js_files',
            array(
                'name' => 'js_files_uploader',
                'label' => __('Select JS Files to Upload'),
                'title' => __('Select JS Files to Upload'),
                'accept' => 'application/x-javascript',
                'multiple' => '',
                'note' => $this->_getUploadJsFileNote()
            )
        );

        $themeFieldset->addField(
            'js_uploader_button',
            'button',
            array('name' => 'js_uploader_button', 'value' => __('Upload JS Files'), 'disabled' => 'disabled')
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
        $fileElement = 'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File';
        return array('js_files' => $fileElement);
    }

    /**
     * Return Tab label
     *
     * @return string
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
            array('id' => $this->_getCurrentTheme()->getId())
        );
    }

    /**
     * Get note string for js file to Upload
     *
     * @return string
     */
    protected function _getUploadJsFileNote()
    {
        return __('Allowed file types *.js.');
    }
}
