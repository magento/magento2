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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer Show Customer Model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_System_Config_Backend_Customer_Show_Customer extends Mage_Core_Model_Config_Data
{
    /**
     * Retrieve attribute code
     *
     * @return string
     */
    protected function _getAttributeCode()
    {
        return str_replace('_show', '', $this->getField());
    }

    /**
     * Retrieve attribute objects
     *
     * @return array
     */
    protected function _getAttributeObjects()
    {
        return array(
            Mage::getSingleton('Mage_Eav_Model_Config')->getAttribute('customer', $this->_getAttributeCode())
        );
    }

    /**
     * Actions after save
     *
     * @return Mage_Adminhtml_Model_System_Config_Backend_Customer_Show_Customer
     */
    protected function _afterSave()
    {
        $result = parent::_afterSave();

        $valueConfig = array(
            ''    => array('is_required' => 0, 'is_visible' => 0),
            'opt' => array('is_required' => 0, 'is_visible' => 1),
            '1'   => array('is_required' => 0, 'is_visible' => 1),
            'req' => array('is_required' => 1, 'is_visible' => 1),
        );

        $value = $this->getValue();
        if (isset($valueConfig[$value])) {
            $data = $valueConfig[$value];
        } else {
            $data = $valueConfig[''];
        }

        if ($this->getScope() == 'websites') {
            $website = Mage::app()->getWebsite($this->getWebsiteCode());
            $dataFieldPrefix = 'scope_';
        } else {
            $website = null;
            $dataFieldPrefix = '';
        }

        foreach ($this->_getAttributeObjects() as $attributeObject) {
            if ($website) {
                $attributeObject->setWebsite($website);
                $attributeObject->load($attributeObject->getId());
            }
            $attributeObject->setData($dataFieldPrefix . 'is_required', $data['is_required']);
            $attributeObject->setData($dataFieldPrefix . 'is_visible',  $data['is_visible']);
            $attributeObject->save();
        }

        return $result;
    }

    /**
     * Processing object after delete data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterDelete()
    {
        $result = parent::_afterDelete();

        if ($this->getScope() == 'websites') {
            $website = Mage::app()->getWebsite($this->getWebsiteCode());
            foreach ($this->_getAttributeObjects() as $attributeObject) {
                $attributeObject->setWebsite($website);
                $attributeObject->load($attributeObject->getId());
                $attributeObject->setData('scope_is_required', null);
                $attributeObject->setData('scope_is_visible',  null);
                $attributeObject->save();
            }
        }

        return $result;
    }
}
