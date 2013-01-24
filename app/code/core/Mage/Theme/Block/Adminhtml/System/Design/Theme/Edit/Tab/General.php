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
 * @category    Mage
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme form, general tab
 */
class Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_General
    extends Mage_Backend_Block_Widget_Form
    implements Mage_Backend_Block_Widget_Tab_Interface
{
    /**
     * Whether theme is editable
     *
     * @var bool
     */
    protected $_isThemeEditable = false;

    /**
     * Create a form element with necessary controls
     *
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_General|Mage_Backend_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        /** @var $session Mage_Backend_Model_Session */
        $session = Mage::getSingleton('Mage_Backend_Model_Session');
        $formDataFromSession = $session->getThemeData();
        $this->_isThemeEditable = $this->_getCurrentTheme()->isVirtual();
        $formData = $this->_getCurrentTheme()->getData();
        if ($formDataFromSession && isset($formData['theme_id'])) {
            unset($formDataFromSession['preview_image']);
            $formData = array_merge($formData, $formDataFromSession);
            $session->setThemeData(null);
        }
        $this->setIsThemeExist(isset($formData['theme_id']));

        $form = new Varien_Data_Form();

        $this->_addThemeFieldset($form, $formData)->_addRequirementsFieldset($form);

        if (!$this->getIsThemeExist()) {
            $formData = array_merge($formData, $this->_getDefaults());
        }
        $form->addValues($formData);
        $form->setFieldNameSuffix('theme');
        $this->setForm($form);

        return $this;
    }

    /**
     * Add theme fieldset
     *
     * @param Varien_Data_Form $form
     * @param array $formData
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_General
     */
    protected function _addThemeFieldset($form, $formData)
    {
        $themeFieldset = $form->addFieldset('theme', array(
            'legend'   => $this->__('Theme Settings'),
        ));
        $this->_addElementTypes($themeFieldset);

        if (isset($formData['theme_id'])) {
            $themeFieldset->addField('theme_id', 'hidden', array(
                'name' => 'theme_id'
            ));
        }

        /** @var $themesCollections Mage_Core_Model_Theme_Collection */
        $themesCollections = Mage::getResourceModel('Mage_Core_Model_Theme_Collection');

        /** @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('Mage_Core_Helper_Data');

        $onChangeScript = sprintf('parentThemeOnChange(this.value, %s)', str_replace(
            '"', '\'', $helper->jsonEncode($this->_getDefaultsInherited($themesCollections->addDefaultPattern()))
        ));

        /** @var $parentTheme Mage_Core_Model_Theme */
        $parentTheme = Mage::getModel('Mage_Core_Model_Theme');
        if (!empty($formData['parent_id'])) {
            $parentTheme->load($formData['parent_id']);
        }

        if ($this->_isThemeEditable) {
            $themeFieldset->addField('parent_id', 'select', array(
                'label'    => $this->__('Parent theme'),
                'title'    => $this->__('Parent theme'),
                'name'     => 'parent_id',
                'values'   => $themesCollections->toOptionArray(!$parentTheme->getId()),
                'required' => true,
                'class'    => 'no-changes',
                'onchange' => $onChangeScript
            ));
        } else if (!empty($formData['parent_id'])) {
            $themeFieldset->addField('parent_title', 'note', array(
                'label'    => $this->__('Parent theme'),
                'title'    => $this->__('Parent theme'),
                'name'     => 'parent_title',
                'text'     => $parentTheme->getId() ? $parentTheme->getThemeTitle() : ''
            ));
        }

        if (!empty($formData['theme_path'])) {
            $themeFieldset->addField('theme_path', 'label', array(
                'label'    => $this->__('Theme Path'),
                'title'    => $this->__('Theme Path'),
                'name'     => 'theme_code',
            ));
        }

        $themeFieldset->addField('theme_version', $this->_getFieldTextType(), array(
            'label'    => $this->__('Theme Version'),
            'title'    => $this->__('Theme Version'),
            'name'     => 'theme_version',
            'required' => $this->_isFieldAttrRequired(),
            'note'     => $this->_filterFieldNote($this->__('Example: 0.0.0.1 or 123.1.0.25-alpha1'))
        ));

        $themeFieldset->addField('theme_title', $this->_getFieldTextType(), array(
            'label'    => $this->__('Theme Title'),
            'title'    => $this->__('Theme Title'),
            'name'     => 'theme_title',
            'required' => $this->_isFieldAttrRequired()
        ));

        if ($this->_isThemeEditable) {
            $themeFieldset->addField('preview_image', 'image', array(
                'label'    => $this->__('Theme Preview Image'),
                'title'    => $this->__('Theme Preview Image'),
                'name'     => 'preview_image',
                'required' => false,
                'note'     => $this->_getPreviewImageNote()
            ));
        } else if (!empty($formData['preview_image'])) {
            $themeFieldset->addField('preview_image', 'note', array(
                'label'    => $this->__('Theme Preview Image'),
                'title'    => $this->__('Theme Preview Image'),
                'name'     => 'preview_image',
                'after_element_html' => '<img width="50" src="' . Mage_Core_Model_Theme::getPreviewImageDirectoryUrl()
                    . $formData['preview_image'] . '" />'
            ));
        }

        return $this;
    }

    /**
     * Add requirements fieldset
     *
     * @param Varien_Data_Form $form
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_General
     */
    protected function _addRequirementsFieldset($form)
    {
        $requirementsFieldset = $form->addFieldset('requirements', array(
            'legend'   => $this->__('Magento Requirements'),
        ));

        $requirementsFieldset->addField('magento_version_from', $this->_getFieldTextType(), array(
            'label'    => $this->__('Magento Version From'),
            'title'    => $this->__('Magento Version From'),
            'name'     => 'magento_version_from',
            'required' => $this->_isFieldAttrRequired(),
            'note'     => $this->_filterFieldNote($this->__('Example: 1.6.0.0 or *'))
        ));

        $requirementsFieldset->addField('magento_version_to', $this->_getFieldTextType(), array(
            'label'    => $this->__('Magento Version To'),
            'title'    => $this->__('Magento Version To'),
            'name'     => 'magento_version_to',
            'required' => $this->_isFieldAttrRequired(),
            'note'     => $this->_filterFieldNote($this->__('Example: 1.6.0.0 or *'))
        ));

        return $this;
    }

    /**
     * No field notes if theme is not editable
     *
     * @param $text
     * @return string
     */
    protected function _filterFieldNote($text)
    {
        return $this->_isThemeEditable ? $text : '';
    }

    /**
     * Field is not marked as required if theme is not editable
     *
     * @return bool
     */
    protected function _isFieldAttrRequired()
    {
        return $this->_isThemeEditable ? true : false;
    }

    /**
     * Text field replaced to label if theme is not editable
     *
     * @return string
     */
    protected function _getFieldTextType()
    {
        return $this->_isThemeEditable ? 'text' : 'label';
    }

    /**
     * Set additional form field type for theme preview image
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $element = Mage::getConfig()
            ->getBlockClassName('Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Form_Element_Image');
        return array('image' => $element);
    }

    /**
     * Get current theme
     *
     * @return Mage_Core_Model_Theme
     */
    protected function _getCurrentTheme()
    {
        return Mage::registry('current_theme');
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('General');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get theme default values
     *
     * @return array
     */
    protected function _getDefaults()
    {
        $defaults = array();
        $defaults['magento_version_from'] = Mage::getVersion();
        $defaults['magento_version_to'] = '*';
        $defaults['theme_version'] = '0.0.0.1';
        $defaults['theme_title'] = $this->__('New theme');

        return $defaults;
    }

    /**
     * Get theme default values while inheriting other theme
     *
     * @param $themesCollections
     * @return array
     */
    protected function _getDefaultsInherited($themesCollections)
    {
        $data = array('' => $this->_getDefaults());

        /** @var $theme Mage_Core_Model_Theme */
        foreach ($themesCollections as $theme) {
            $theme->load($theme->getThemePath(), 'theme_path');
            if (!$theme->getId()) {
                continue;
            }
            $data[$theme->getId()] = array(
                'theme_title'          => $this->__('Copy of %s', $theme->getThemeTitle()),
                'magento_version_from' => $theme->getMagentoVersionFrom(),
                'magento_version_to'   => $theme->getMagentoVersionTo()
            );
        }

        return $data;
    }

    /**
     * Get note string for theme's preview image
     *
     * @return string
     */
    protected function _getPreviewImageNote()
    {
        $maxImageSize = Mage::getObjectManager()->get('Magento_File_Size')->getMaxFileSizeInMb();
        if ($maxImageSize) {
            return $this->__('Max image size %sM', $maxImageSize);
        } else {
            return $this->__('System doesn\'t allow to get file upload settings');
        }
    }
}
