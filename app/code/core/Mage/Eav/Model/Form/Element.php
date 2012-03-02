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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Eav Form Element Model
 *
 * @method Mage_Eav_Model_Resource_Form_Element _getResource()
 * @method Mage_Eav_Model_Resource_Form_Element getResource()
 * @method int getTypeId()
 * @method Mage_Eav_Model_Form_Element setTypeId(int $value)
 * @method int getFieldsetId()
 * @method Mage_Eav_Model_Form_Element setFieldsetId(int $value)
 * @method int getAttributeId()
 * @method Mage_Eav_Model_Form_Element setAttributeId(int $value)
 * @method int getSortOrder()
 * @method Mage_Eav_Model_Form_Element setSortOrder(int $value)
 *
 * @category    Mage
 * @package     Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Form_Element extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'eav_form_element';

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Eav_Model_Resource_Form_Element');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return Mage_Eav_Model_Resource_Form_Element
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Retrieve resource collection instance wrapper
     *
     * @return Mage_Eav_Model_Resource_Form_Element_Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Validate data before save data
     *
     * @throws Mage_Core_Exception
     * @return Mage_Eav_Model_Form_Element
     */
    protected function _beforeSave()
    {
        if (!$this->getTypeId()) {
            Mage::throwException(Mage::helper('Mage_Eav_Helper_Data')->__('Invalid form type.'));
        }
        if (!$this->getAttributeId()) {
            Mage::throwException(Mage::helper('Mage_Eav_Helper_Data')->__('Invalid EAV attribute.'));
        }

        return parent::_beforeSave();
    }

    /**
     * Retrieve EAV Attribute instance
     *
     * @return Mage_Eav_Model_Entity_Attribute
     */
    public function getAttribute()
    {
        if (!$this->hasData('attribute')) {
            $attribute = Mage::getSingleton('Mage_Eav_Model_Config')
                ->getAttribute($this->getEntityTypeId(), $this->getAttributeId());
            $this->setData('attribute', $attribute);
        }
        return $this->_getData('attribute');
    }
}
