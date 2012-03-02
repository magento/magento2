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
 * Convert profile edit tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_Run extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('system/convert/profile/run.phtml');
    }

    public function getRunButtonHtml()
    {
        $html = '';
/*
        if (Mage::registry('current_convert_profile')->getDirection()=='import') {
            $html .= $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')->setType('button')
                ->setLabel($this->__('Upload import file'))
                ->setOnClick('showUpload()')
                ->toHtml();
        }
*/
        /*
        $html .= $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')->setType('button')
            ->setClass('save')->setLabel($this->__('Run Profile Inside This Window'))
            ->setOnClick('runProfile()')
            ->toHtml();
        */

        $html .= $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')->setType('button')
            ->setClass('save')->setLabel($this->__('Run Profile in Popup'))
            ->setOnClick('runProfile(true)')
            ->toHtml();

        return $html;
    }

    public function getProfileId()
    {
        return Mage::registry('current_convert_profile')->getId();
    }

    public function getImportedFiles()
    {
        $files = array();
        $path = Mage::app()->getConfig()->getTempVarDir().'/import';
        if (!is_readable($path)) {
            return $files;
        }
        $dir = dir($path);
        while (false !== ($entry = $dir->read())) {
            if($entry != '.'
               && $entry != '..'
               && in_array(strtolower(substr($entry, strrpos($entry, '.')+1)), array($this->getParseType())))
            {
                $files[] = $entry;
            }
        }
        sort($files);
        $dir->close();
        return $files;
    }

    public function getParseType()
    {
        $data = Mage::registry('current_convert_profile')->getGuiData();
        if ($data)
            return ($data['parse']['type'] == 'excel_xml') ? 'xml': $data['parse']['type'];
    }
}
