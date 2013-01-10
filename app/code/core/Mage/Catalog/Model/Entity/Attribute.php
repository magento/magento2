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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attribute extension with event dispatching
 *
 * @method Mage_Catalog_Model_Resource_Attribute _getResource()
 * @method Mage_Catalog_Model_Resource_Attribute getResource()
 * @method string getFrontendInputRenderer()
 * @method Mage_Catalog_Model_Entity_Attribute setFrontendInputRenderer(string $value)
 * @method int setIsGlobal(int $value)
 * @method int getIsVisible()
 * @method int setIsVisible(int $value)
 * @method int getIsSearchable()
 * @method Mage_Catalog_Model_Entity_Attribute setIsSearchable(int $value)
 * @method int getSearchWeight()
 * @method Mage_Catalog_Model_Entity_Attribute setSearchWeight(int $value)
 * @method int getIsFilterable()
 * @method Mage_Catalog_Model_Entity_Attribute setIsFilterable(int $value)
 * @method int getIsComparable()
 * @method Mage_Catalog_Model_Entity_Attribute setIsComparable(int $value)
 * @method Mage_Catalog_Model_Entity_Attribute setIsVisibleOnFront(int $value)
 * @method int getIsHtmlAllowedOnFront()
 * @method Mage_Catalog_Model_Entity_Attribute setIsHtmlAllowedOnFront(int $value)
 * @method int getIsUsedForPriceRules()
 * @method Mage_Catalog_Model_Entity_Attribute setIsUsedForPriceRules(int $value)
 * @method int getIsFilterableInSearch()
 * @method Mage_Catalog_Model_Entity_Attribute setIsFilterableInSearch(int $value)
 * @method int getUsedInProductListing()
 * @method Mage_Catalog_Model_Entity_Attribute setUsedInProductListing(int $value)
 * @method int getUsedForSortBy()
 * @method Mage_Catalog_Model_Entity_Attribute setUsedForSortBy(int $value)
 * @method int getIsConfigurable()
 * @method Mage_Catalog_Model_Entity_Attribute setIsConfigurable(int $value)
 * @method string getApplyTo()
 * @method Mage_Catalog_Model_Entity_Attribute setApplyTo(string $value)
 * @method int getIsVisibleInAdvancedSearch()
 * @method Mage_Catalog_Model_Entity_Attribute setIsVisibleInAdvancedSearch(int $value)
 * @method int getPosition()
 * @method Mage_Catalog_Model_Entity_Attribute setPosition(int $value)
 * @method int getIsWysiwygEnabled()
 * @method Mage_Catalog_Model_Entity_Attribute setIsWysiwygEnabled(int $value)
 * @method int getIsUsedForPromoRules()
 * @method Mage_Catalog_Model_Entity_Attribute setIsUsedForPromoRules(int $value)
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Entity_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    protected $_eventPrefix = 'catalog_entity_attribute';
    protected $_eventObject = 'attribute';
    const MODULE_NAME = 'Mage_Catalog';

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->_getResource()->isUsedBySuperProducts($this)) {
            throw Mage::exception('Mage_Eav', Mage::helper('Mage_Eav_Helper_Data')->__('This attribute is used in configurable products'));
        }
        $this->setData('modulePrefix', self::MODULE_NAME);
        return parent::_beforeSave();
    }

    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        /**
         * Fix saving attribute in admin
         */
        Mage::getSingleton('Mage_Eav_Model_Config')->clear();
        return parent::_afterSave();
    }
}
