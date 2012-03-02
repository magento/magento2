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
class Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_Wizard extends Mage_Adminhtml_Block_Widget_Container
{

    protected $_storeModel;
    protected $_attributes;
    protected $_addMapButtonHtml;
    protected $_removeMapButtonHtml;
    protected $_shortDateFormat;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('system/convert/profile/wizard.phtml');
    }

    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setCanLoadCalendarJs(true);
        }
        return $this;
    }

    public function getAttributes($entityType)
    {
        if (!isset($this->_attributes[$entityType])) {
            switch ($entityType) {
                case 'product':
                    $attributes = Mage::getSingleton('Mage_Catalog_Model_Convert_Parser_Product')
                        ->getExternalAttributes();
                    break;

                case 'customer':
                    $attributes = Mage::getSingleton('Mage_Customer_Model_Convert_Parser_Customer')
                        ->getExternalAttributes();
                    break;
            }

            array_splice($attributes, 0, 0, array(''=>$this->__('Choose an attribute')));
            $this->_attributes[$entityType] = $attributes;
        }
        return $this->_attributes[$entityType];
    }

    public function getValue($key, $default='', $defaultNew = null)
    {
        if (null !== $defaultNew) {
            if (0 == $this->getProfileId()) {
                $default = $defaultNew;
            }
        }

        $value = $this->getData($key);
        return $this->escapeHtml(strlen($value) > 0 ? $value : $default);
    }

    public function getSelected($key, $value)
    {
        return $this->getData($key)==$value ? 'selected="selected"' : '';
    }

    public function getChecked($key)
    {
        return $this->getData($key) ? 'checked="checked"' : '';
    }

    public function getMappings($entityType)
    {
        $maps = $this->getData('gui_data/map/'.$entityType.'/db');
        return $maps ? $maps : array();
    }

    public function getAddMapButtonHtml()
    {
        if (!$this->_addMapButtonHtml) {
            $this->_addMapButtonHtml = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                ->setType('button')
                ->setClass('add')->setLabel($this->__('Add Field Mapping'))
                ->setOnClick("addFieldMapping()")->toHtml();
        }
        return $this->_addMapButtonHtml;
    }

    public function getRemoveMapButtonHtml()
    {
        if (!$this->_removeMapButtonHtml) {
            $this->_removeMapButtonHtml = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                ->setType('button')
                ->setClass('delete')->setLabel($this->__('Remove'))
                ->setOnClick("removeFieldMapping(this)")->toHtml();
        }
        return $this->_removeMapButtonHtml;
    }

    public function getProductTypeFilterOptions()
    {
        $options = Mage::getSingleton('Mage_Catalog_Model_Product_Type')->getOptionArray();
        array_splice($options, 0, 0, array(''=>$this->__('Any Type')));
        return $options;
    }

    public function getProductAttributeSetFilterOptions()
    {
        $options = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
            ->setEntityTypeFilter(Mage::getModel('Mage_Catalog_Model_Product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $opt = array();
        $opt = array(''=>$this->__('Any Attribute Set'));
        if ($options) foreach($options as $index => $value) {
            $opt[$index]  = $value;
        }
        //array_slice($options, 0, 0, array(''=>$this->__('Any Attribute Set')));
        return $opt;
    }

    public function getProductVisibilityFilterOptions()
    {
        $options = Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getOptionArray();

        array_splice($options, 0, 0, array(''=>$this->__('Any Visibility')));
        return $options;
    }

    public function getProductStatusFilterOptions()
    {
        $options = Mage::getSingleton('Mage_Catalog_Model_Product_Status')->getOptionArray();

        array_splice($options, 0, 0, array(''=>$this->__('Any Status')));
        return $options;
    }

    public function getStoreFilterOptions()
    {
        if (!$this->_filterStores) {
            #$this->_filterStores = array(''=>$this->__('Any Store'));
            $this->_filterStores = array();
            foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
                if ($storeNode->getName()==='default') {
                    //continue;
                }
                $this->_filterStores[$storeNode->getName()] = (string)$storeNode->system->store->name;
            }
        }
        return $this->_filterStores;
    }

    public function getCustomerGroupFilterOptions()
    {
        $options = Mage::getResourceModel('Mage_Customer_Model_Resource_Group_Collection')
            ->addFieldToFilter('customer_group_id', array('gt'=>0))
            ->load()
            ->toOptionHash();

        array_splice($options, 0, 0, array(''=>$this->__('Any Group')));
        return $options;
    }

    public function getCountryFilterOptions()
    {
        $options = Mage::getResourceModel('Mage_Directory_Model_Resource_Country_Collection')
            ->load()->toOptionArray(false);
        array_unshift($options, array('value'=>'', 'label'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('All countries')));
        return $options;
    }

    /**
     * Retrieve system store model
     *
     * @return Mage_Adminhtml_Model_System_Store
     */
    protected function _getStoreModel() {
        if (is_null($this->_storeModel)) {
            $this->_storeModel = Mage::getSingleton('Mage_Adminhtml_Model_System_Store');
        }
        return $this->_storeModel;
    }

    public function getWebsiteCollection()
    {
        return $this->_getStoreModel()->getWebsiteCollection();
    }

    public function getGroupCollection()
    {
        return $this->_getStoreModel()->getGroupCollection();
    }

    public function getStoreCollection()
    {
        return $this->_getStoreModel()->getStoreCollection();
    }

    public function getShortDateFormat()
    {
        if (!$this->_shortDateFormat) {
            $this->_shortDateFormat = Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        }
        return $this->_shortDateFormat;
    }

}

