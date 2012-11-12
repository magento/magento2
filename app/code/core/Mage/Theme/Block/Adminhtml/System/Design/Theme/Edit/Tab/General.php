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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
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
     * Create a form element with necessary controls
     *
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_General|Mage_Backend_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        /** @var $session Mage_Backend_Model_Session */
        $session = Mage::getSingleton('Mage_Backend_Model_Session');
        $formDataFromSession = $session->getThemeData();
        $formData = Mage::registry('current_theme')->getData();
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

        /** @var $themesCollections Mage_Core_Model_Resource_Theme_Collection */
        $themesCollections = Mage::getResourceModel('Mage_Core_Model_Resource_Theme_Collection');
        if ($this->getIsThemeExist()) {
            $themesCollections->addFieldToFilter('theme_id', array('neq' => $formData['theme_id']));
            $onChangeScript = '';
        } else {
            /** @var $helper Mage_Core_Helper_Data */
            $helper = Mage::helper('Mage_Core_Helper_Data');

            $onChangeScript = sprintf('parentThemeOnChange(this.value, %s)',
                str_replace('"', '\'', $helper->jsonEncode($this->_getDefaultsInherited($themesCollections)))
            );
        }

        $themeFieldset->addField('parent_id', 'select', array(
            'label'    => $this->__('Parent theme'),
            'title'    => $this->__('Parent theme'),
            'name'     => 'parent_id',
            'values'   => $themesCollections->toOptionArray(),
            'required' => false,
            'class'    => 'no-changes',
            'onchange' => $onChangeScript
        ));

        if (!empty($formData['theme_path'])) {
            $themeFieldset->addField('theme_path', 'label', array(
                'label'    => $this->__('Theme Path'),
                'title'    => $this->__('Theme Path'),
                'name'     => 'theme_code',
            ));
        }

        $themeFieldset->addField('theme_version', 'text', array(
            'label'    => $this->__('Theme Version'),
            'title'    => $this->__('Theme Version'),
            'name'     => 'theme_version',
            'required' => true,
            'note'     => $this->__('Example: 0.0.0.1 or 123.1.0.25-alpha1')
        ));

        $themeFieldset->addField('theme_title', 'text', array(
            'label'    => $this->__('Theme Title'),
            'title'    => $this->__('Theme Title'),
            'name'     => 'theme_title',
            'required' => true
        ));

        $maxImageSize = $this->getImageMaxSize();
        if ($maxImageSize) {
            $previewImageNote = $this->__('Max image size: %s', $maxImageSize);
        } else {
            $previewImageNote = $this->__("System doesn't allow to get file upload settings");
        }
        $themeFieldset->addField('preview_image', 'image', array(
            'label'    => $this->__('Theme Preview Image'),
            'title'    => $this->__('Theme Preview Image'),
            'name'     => 'preview_image',
            'required' => false,
            'note'     => $previewImageNote
        ));

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

        $requirementsFieldset->addField('magento_version_from', 'text', array(
            'label'    => $this->__('Magento Version From'),
            'title'    => $this->__('Magento Version From'),
            'name'     => 'magento_version_from',
            'required' => true,
            'note'     => $this->__('Example: 1.6.0.0 or *')
        ));

        $requirementsFieldset->addField('magento_version_to', 'text', array(
            'label'    => $this->__('Magento Version To'),
            'title'    => $this->__('Magento Version To'),
            'name'     => 'magento_version_to',
            'required' => true,
            'note'     => $this->__('Example: 1.6.0.0 or *')
        ));

        return $this;
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
     * Get max file size
     *
     * @return string|bool
     */
    public function getImageMaxSize()
    {
        return min(ini_get('post_max_size'), ini_get('upload_max_filesize'));
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
        $data = array(
            '' => $this->_getDefaults()
        );
        foreach ($themesCollections as $theme) {
            $data[$theme->getId()] = array(
                'theme_title'          => $this->__('Copy of %s', $theme->getThemeTitle()),
                'magento_version_from' => $theme->getMagentoVersionFrom(),
                'magento_version_to'   => $theme->getMagentoVersionTo()
            );
        }

        return $data;
    }
}
