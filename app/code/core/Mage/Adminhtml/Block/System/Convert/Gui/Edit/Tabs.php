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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * admin customer left menu
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('convert_profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Import/Export Profile'));
    }

    protected function _beforeToHtml()
    {
        $profile = Mage::registry('current_convert_profile');

        $wizardBlock = $this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_Wizard');
        $wizardBlock->addData($profile->getData());

        $new = !$profile->getId();

        $this->addTab('wizard', array(
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Profile Wizard'),
            'content'   => $wizardBlock->toHtml(),
            'active'    => true,
        ));

        if (!$new) {
            if ($profile->getDirection() != 'export') {
                $this->addTab('upload', array(
                    'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Upload File'),
                    'content'   => $this->getLayout()->createBlock('Mage_Backend_Block_Widget_Form')
                        ->setTemplate('system/convert/profile/upload.phtml')->toHtml(),
                ));
            }

            $this->addTab('run', array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Run Profile'),
                'content'   => $this->getLayout()
                    ->createBlock('Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_Run')->toHtml(),
            ));

            $this->addTab('view', array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Profile Actions XML'),
                'content'   => $this->getLayout()
                    ->createBlock('Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_View')->initForm()->toHtml(),
            ));

            $this->addTab('history', array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Profile History'),
                'content'   => $this->getLayout()
                    ->createBlock('Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_History')->toHtml(),
            ));
        }

        return parent::_beforeToHtml();
    }
}
