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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Device design themes block
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Edit_Tab_Design_Themes extends Mage_Adminhtml_Block_Template
{
    /**
     * Set themes template and color fieldsets
     */
    public function __construct()
    {
        parent::__construct();

        try {
            $model = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication();
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return;
        }
        $this->setTemplate('form/element/themes.phtml');

        $data = $model->getFormData();
        $this->setColorFieldset(array(
            array('id' => 'field_colors', 'label' => $this->__('Colors'), 'fields' => array(
                $this->_addColorBox(
                    'conf[native][navigationBar][tintColor]',
                    $this->__('Header Background Color'),
                    $data
                ),
                $this->_addColorBox(
                    'conf[native][body][primaryColor]',
                    $this->__('Primary Color'),
                    $data
                ),
                $this->_addColorBox(
                    'conf[native][body][secondaryColor]',
                    $this->__('Secondary Color'),
                    $data
                ),
                $this->_addColorBox(
                    'conf[native][categoryItem][backgroundColor]',
                    $this->__('Category Item Background Color'),
                    $data
                ),
                $this->_addColorBox(
                    'conf[native][categoryItem][tintColor]',
                    $this->__('Category Button Color'),
                    $data
                ),
            )),
            array('id' => 'field_fonts', 'label' => $this->__('Fonts'), 'fields' => array(
                $this->_addColorBox(
                    'conf[extra][fontColors][header]',
                    $this->__('Header Font Color'),
                    $data
                ),
                $this->_addColorBox(
                    'conf[extra][fontColors][primary]',
                    $this->__('Primary Font Color'),
                    $data
                ),
                $this->_addColorBox(
                    'conf[extra][fontColors][secondary]',
                    $this->__('Secondary Font Color'),
                    $data
                ),
                $this->_addColorBox(
                    'conf[extra][fontColors][price]',
                    $this->__('Price Font Color'),
                    $data
                ),
            )),
            array('id' => 'field_advanced', 'label' => $this->__('Advanced Settings'), 'fields' => array(
                $this->_addColorBox(
                    'conf[native][body][backgroundColor]',
                    $this->__('Background Color'),
                    $data
                ),
                $this->_addColorBox(
                    'conf[native][body][scrollBackgroundColor]',
                    $this->__('Scroll Background Color'),
                    $data
                ),
                $this->_addColorBox(
                    'conf[native][itemActions][relatedProductBackgroundColor]',
                    $this->__('Related Product Background Color'),
                    $data
                ),
            )),
        ));
    }

    /**
     * Themes array getter
     *
     * @return array
     */
    public function getAllThemes()
    {
        $result = array();

        foreach ($this->getThemes() as $theme) {
            $result[$theme->getName()] = $theme->getFormData();
        }
        return $result;
    }

    /**
     * Create color field params
     *
     * @param id $id
     * @param string $label
     * @param array $data
     * @return array
     */
    protected function _addColorBox($id, $label, $data)
    {
        return array(
            'id'    => $id,
            'name'  => $id,
            'label' => $label,
            'value' => isset($data[$id]) ? $data[$id] : ''
        );
    }

    /**
     * Getter, check if it's needed to load default theme config
     *
     * @return bool
     */
    public function getDefaultThemeLoaded()
    {
        return Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication()->getDefaultThemeLoaded();
    }

    /**
     * Check if adding new Application
     *
     * @return bool
     */
    public function isNewApplication()
    {
        return Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication()->getId() ? false : true;
    }

    /**
     * Save theme action url getter
     *
     * @return string
     */
    public function getSaveThemeActionUrl()
    {
        return $this->getUrl('*/*/saveTheme');
    }

    /**
     * Get delete theme action url
     *
     * @return string
     */
    public function getDeleteThemeActionUrl()
    {
        return $this->getUrl('*/*/deleteTheme');
    }

    /**
     * Reset theme action url getter
     *
     * @return string
     */
    public function getResetThemeActionUrl()
    {
        return $this->getUrl('*/*/resetTheme');
    }
}
