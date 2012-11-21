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
 * @package     Mage_Install
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Config installation block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Install_Block_Config extends Mage_Install_Block_Abstract
{
    protected $_template = 'config.phtml';

    /**
     * Retrieve form data post url
     *
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('*/*/configPost');
    }

    /**
     * Retrieve configuration form data object
     *
     * @return Varien_Object
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $data = Mage::getSingleton('Mage_Install_Model_Session')->getConfigData(true);
            if (empty($data)) {
                $data = Mage::getModel('Mage_Install_Model_Installer_Config')->getFormData();
            }
            else {
                $data = new Varien_Object($data);
            }
            $this->setFormData($data);
        }
        return $data;
    }

    public function getSkipUrlValidation()
    {
        return Mage::getSingleton('Mage_Install_Model_Session')->getSkipUrlValidation();
    }

    public function getSkipBaseUrlValidation()
    {
        return Mage::getSingleton('Mage_Install_Model_Session')->getSkipBaseUrlValidation();
    }

    public function getSessionSaveOptions()
    {
        return array(
            'files' => Mage::helper('Mage_Install_Helper_Data')->__('File System'),
            'db'    => Mage::helper('Mage_Install_Helper_Data')->__('Database'),
        );
    }

    public function getSessionSaveSelect()
    {
        $html = $this->getLayout()->createBlock('Mage_Core_Block_Html_Select')
            ->setName('config[session_save]')
            ->setId('session_save')
            ->setTitle(Mage::helper('Mage_Install_Helper_Data')->__('Save Session Files In'))
            ->setClass('required-entry')
            ->setOptions($this->getSessionSaveOptions())
            ->getHtml();
        return $html;
    }
}
