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
 * Adminhtml tier price item renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Form element instance
     *
     * @var Varien_Data_Form_Element
     */
    protected $_element;

    /**
     * Customer Groups cache
     *
     * @var array
     */
    protected $_customerGroups;

    /**
     * Websites cache
     *
     * @var array
     */
    protected $_websites;

    /**
     * Define tier price template file
     *
     */
    public function __construct()
    {
        $this->setTemplate('catalog/product/edit/price/tier.phtml');
    }

    /**
     * Retrieve current edit product instance
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Render HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Set form element instance
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * Retrieve form element instance
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Prepare Tier Price values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array();
        $data   = $this->getElement()->getValue();

        if (is_array($data)) {
            usort($data, array($this, '_sortTierPrices'));
            $values = $data;
        }

        foreach ($values as &$v) {
            $v['readonly'] = $v['website_id'] == 0 && $this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite();
        }

        return $values;
    }

    /**
     * Sort tier price values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortTierPrices($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? -1 : 1;
        }
        if ($a['cust_group'] != $b['cust_group']) {
            return $this->getCustomerGroups($a['cust_group']) < $this->getCustomerGroups($b['cust_group']) ? -1 : 1;
        }
        if ($a['price_qty'] != $b['price_qty']) {
            return $a['price_qty'] < $b['price_qty'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId  return name by customer group id
     * @return array|string
     */
    public function getCustomerGroups($groupId = null)
    {
        if ($this->_customerGroups === null) {
            if (!Mage::helper('Mage_Catalog_Helper_Data')->isModuleEnabled('Mage_Customer')) {
                return array();
            }
            $collection = Mage::getModel('Mage_Customer_Model_Group')->getCollection();
            $this->_customerGroups = array(
                Mage_Customer_Model_Group::CUST_GROUP_ALL => Mage::helper('Mage_Catalog_Helper_Data')->__('ALL GROUPS')
            );

            foreach ($collection as $item) {
                /* @var $item Mage_Customer_Model_Group */
                $this->_customerGroups[$item->getId()] = $item->getCustomerGroupCode();
            }
        }

        if ($groupId !== null) {
            return isset($this->_customerGroups[$groupId]) ? $this->_customerGroups[$groupId] : array();
        }

        return $this->_customerGroups;
    }

    /**
     * Retrieve count of websites
     *
     * @return int
     */
    public function getWebsiteCount()
    {
        return count($this->getWebsites());
    }

    /**
     * Show Website column and switcher for tier price table
     *
     * @return bool
     */
    public function isMultiWebsites()
    {
        return !Mage::app()->isSingleStoreMode();
    }

    /**
     * Retrieve allowed for edit websites
     *
     * @return array
     */
    public function getWebsites()
    {
        if (!is_null($this->_websites)) {
            return $this->_websites;
        }

        $this->_websites = array(
            0   => array(
                'name'      => Mage::helper('Mage_Catalog_Helper_Data')->__('All Websites'),
                'currency'  => Mage::app()->getBaseCurrencyCode()
            )
        );

        if (!$this->isScopeGlobal() && $this->getProduct()->getStoreId()) {
            /* @var $website Mage_Core_Model_Website */
            $website = Mage::app()->getStore($this->getProduct()->getStoreId())->getWebsite();

            $this->_websites[$website->getId()] = array(
                'name'      => $website->getName(),
                'currency'  => $website->getBaseCurrencyCode()
            );
        } else if (!$this->isScopeGlobal()) {
            $websites           = Mage::app()->getWebsites(false);
            $productWebsiteIds  = $this->getProduct()->getWebsiteIds();
            foreach ($websites as $website) {
                /* @var $website Mage_Core_Model_Website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $this->_websites[$website->getId()] = array(
                    'name'      => $website->getName(),
                    'currency'  => $website->getBaseCurrencyCode()
                );
            }
        }

        return $this->_websites;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    public function getDefaultCustomerGroup()
    {
        return Mage_Customer_Model_Group::CUST_GROUP_ALL;
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     */
    public function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return Mage::app()->getStore($this->getProduct()->getStoreId())->getWebsiteId();
        }
        return 0;
    }

    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
            ->setData(array(
                'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Add Tier'),
                'onclick'   => 'return tierPriceControl.addItem()',
                'class'     => 'add'
            ));
        $button->setName('add_tier_price_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Retrieve Add Tier Price Item button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Returns customized price column header
     * that was seted through set...
     *
     * @param string $default
     * @return string
     */
    public function getPriceColumnHeader($default)
    {
        if ($this->hasData('price_column_header')) {
            return $this->getData('price_column_header');
        } else {
            return $default;
        }
    }

    /**
     * Returns customized price column header
     * that was seted through set...
     *
     * @param string $default
     * @return string
     */
    public function getPriceValidation($default)
    {
        if ($this->hasData('price_validation')) {
            return $this->getData('price_validation');
        } else {
            return $default;
        }
    }

    /**
     * Retrieve Tier Price entity attribute
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute()
    {
        return $this->getElement()->getEntityAttribute();
    }

    /**
     * Check tier price attribute scope is global
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getAttribute()->isScopeGlobal();
    }

    /**
     * Show tier prices grid website column
     *
     * @return bool
     */
    public function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal()) {
            return false;
        } else if (Mage::app()->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    public function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->getProduct()->getStoreId()) {
            return false;
        }
        return true;
    }
}
