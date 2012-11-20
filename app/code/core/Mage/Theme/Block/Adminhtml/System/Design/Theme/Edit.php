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
 * Theme editor container
 */
class Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit extends Mage_Backend_Block_Widget_Form_Container
{
    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->_blockGroup = 'Mage_Theme';
        $this->_controller = 'Adminhtml_System_Design_Theme';
        $this->setId('theme_edit');

        /** @var $theme Mage_Core_Model_Theme */
        $theme = Mage::registry('current_theme');
        if ($theme && !$theme->isVirtual()) {
            $this->_removeButton('delete');
            $this->_removeButton('save');
            $this->_removeButton('reset');
        } else {
            $this->_addButton('save_and_continue', array(
                'label'   => $this->__('Save and Continue Edit'),
                'onclick' => "editForm.submit($('edit_form').action+'back/edit/');",
                'class'   => 'save',
            ), 1);

            if ($theme->hasChildThemes()) {
                $onClick = 'deleteConfirm(\'' . $this->__('Theme contains child themes. Their parent will be modified.')
                    . ' ' . $this->__('Are you sure you want to do this?')
                    . '\', \'' . $this->getUrl('*/*/delete', array('id' => $theme->getId())) . '\')';

                $this->_updateButton('delete', 'onclick', $onClick);
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare header for container
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_theme')->getId()) {
            $header = $this->__('Theme: %s', Mage::registry('current_theme')->getThemeTitle());
        } else {
            $header = $this->__('New Theme');
        }
        return $header;
    }
}
