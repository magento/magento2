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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block that renders Custom tab
 *
 * @method Mage_Core_Model_Theme getTheme()
 * @method setTheme($theme)
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Custom extends Mage_Backend_Block_Widget_Form
{
    /**
     * Upload file element html id
     */
    const FILE_ELEMENT_NAME = 'css_file_uploader';

    /**
     * Create a form element with necessary controls
     *
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_Css
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'action'   => '#',
            'method'   => 'post'
        ));
        $this->setForm($form);
        $form->setUseContainer(true);

        $form->addType('css_file', 'Mage_DesignEditor_Block_Adminhtml_Editor_Form_Element_File');

        $form->addField($this->getFileElementName(), 'css_file', array(
            'name'     => $this->getFileElementName(),
            'accept'   => 'text/css',
            'no_span'  => true
        ));

        parent::_prepareForm();
        return $this;
    }

    /**
     * Get url to download custom CSS file
     *
     * @param Mage_Core_Model_Theme $theme
     * @return string
     */
    public function getDownloadCustomCssUrl($theme)
    {
        return $this->getUrl('*/system_design_theme/downloadCustomCss', array('theme_id' => $theme->getThemeId()));
    }

    /**
     * Get url to save custom CSS file
     *
     * @param Mage_Core_Model_Theme $theme
     * @return string
     */
    public function getSaveCustomCssUrl($theme)
    {
        return $this->getUrl('*/system_design_editor_tools/saveCssContent', array('theme_id' => $theme->getThemeId()));
    }

    /**
     * Get theme custom css content
     *
     * @param Mage_Core_Model_Theme $theme
     * @return string
     */
    public function getCustomCssContent($theme)
    {
        /** @var $cssFile Mage_Core_Model_Theme_Files */
        $cssFile = $theme->getCustomizationData(Mage_Core_Model_Theme_Customization_Files_Css::TYPE)->getFirstItem();
        return $cssFile->getContent();
    }

    /**
     * Get custom CSS file name
     *
     * @return string
     */
    public function getCustomFileName()
    {
        return pathinfo(Mage_Core_Model_Theme_Customization_Files_Css::FILE_PATH, PATHINFO_BASENAME);
    }

    /**
     * Get file element name
     *
     * @return string
     */
    public function getFileElementName()
    {
        return self::FILE_ELEMENT_NAME;
    }
}
