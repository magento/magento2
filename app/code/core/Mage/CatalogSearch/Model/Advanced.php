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
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog advanced search model
 *
 * @method Mage_CatalogSearch_Model_Resource_Advanced _getResource()
 * @method Mage_CatalogSearch_Model_Resource_Advanced getResource()
 * @method int getEntityTypeId()
 * @method Mage_CatalogSearch_Model_Advanced setEntityTypeId(int $value)
 * @method int getAttributeSetId()
 * @method Mage_CatalogSearch_Model_Advanced setAttributeSetId(int $value)
 * @method string getTypeId()
 * @method Mage_CatalogSearch_Model_Advanced setTypeId(string $value)
 * @method string getSku()
 * @method Mage_CatalogSearch_Model_Advanced setSku(string $value)
 * @method int getHasOptions()
 * @method Mage_CatalogSearch_Model_Advanced setHasOptions(int $value)
 * @method int getRequiredOptions()
 * @method Mage_CatalogSearch_Model_Advanced setRequiredOptions(int $value)
 * @method string getCreatedAt()
 * @method Mage_CatalogSearch_Model_Advanced setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Mage_CatalogSearch_Model_Advanced setUpdatedAt(string $value)
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogSearch_Model_Advanced extends Mage_Core_Model_Abstract
{
    /**
     * User friendly search criteria list
     *
     * @var array
     */
    protected $_searchCriterias = array();

    /**
     * Current search engine
     *
     * @var object | Mage_CatalogSearch_Model_Resource_Fulltext_Engine
     */
    protected $_engine = null;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_getEngine();
        $this->_init('Mage_CatalogSearch_Model_Resource_Advanced');
    }

    protected function _getEngine()
    {
        if ($this->_engine == null) {
            $this->_engine = Mage::helper('Mage_CatalogSearch_Helper_Data')->getEngine();
        }

        return $this->_engine;
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return Mage_CatalogSearch_Model_Resource_Advanced
     */
    protected function _getResource()
    {
        $resourceName = $this->_engine->getResourceName();
        if ($resourceName) {
            $this->_resourceName = $resourceName;
        }
        return parent::_getResource();
    }

    /**
     * Retrieve array of attributes used in advanced search
     *
     * @return array
     */
    public function getAttributes()
    {
        /* @var $attributes Mage_Catalog_Model_Resource_Eav_Resource_Product_Attribute_Collection */
        $attributes = $this->getData('attributes');
        if (is_null($attributes)) {
            $product = Mage::getModel('Mage_Catalog_Model_Product');
            $attributes = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Attribute_Collection')
                ->addHasOptionsFilter()
                ->addDisplayInAdvancedSearchFilter()
                ->addStoreLabel(Mage::app()->getStore()->getId())
                ->setOrder('main_table.attribute_id', 'asc')
                ->load();
            foreach ($attributes as $attribute) {
                $attribute->setEntity($product->getResource());
            }
            $this->setData('attributes', $attributes);
        }
        return $attributes;
    }

    /**
     * Add advanced search filters to product collection
     *
     * @param   array $values
     * @return  Mage_CatalogSearch_Model_Advanced
     */
    public function addFilters($values)
    {
        $attributes     = $this->getAttributes();
        $hasConditions  = false;
        $allConditions  = array();

        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if (!isset($values[$attribute->getAttributeCode()])) {
                continue;
            }
            $value = $values[$attribute->getAttributeCode()];

            if ($attribute->getAttributeCode() == 'price') {
                $value['from'] = isset($value['from']) ? trim($value['from']) : '';
                $value['to'] = isset($value['to']) ? trim($value['to']) : '';
                if (is_numeric($value['from']) || is_numeric($value['to'])) {
                    if (!empty($value['currency'])) {
                        $rate = Mage::app()->getStore()->getBaseCurrency()->getRate($value['currency']);
                    } else {
                        $rate = 1;
                    }
                    if ($this->_getResource()->addRatedPriceFilter(
                        $this->getProductCollection(), $attribute, $value, $rate)
                    ) {
                        $hasConditions = true;
                        $this->_addSearchCriteria($attribute, $value);
                    }
                }
            } else if ($attribute->isIndexable()) {
                if (!is_string($value) || strlen($value) != 0) {
                    if ($this->_getResource()->addIndexableAttributeModifiedFilter(
                        $this->getProductCollection(), $attribute, $value)) {
                        $hasConditions = true;
                        $this->_addSearchCriteria($attribute, $value);
                    }
                }
            } else {
                $condition = $this->_getResource()->prepareCondition($attribute, $value, $this->getProductCollection());
                if ($condition === false) {
                    continue;
                }

                $this->_addSearchCriteria($attribute, $value);

                $table = $attribute->getBackend()->getTable();
                if ($attribute->getBackendType() == 'static'){
                    $attributeId = $attribute->getAttributeCode();
                } else {
                    $attributeId = $attribute->getId();
                }
                $allConditions[$table][$attributeId] = $condition;
            }
        }
        if ($allConditions) {
            $this->getProductCollection()->addFieldsToFilter($allConditions);
        } else if (!$hasConditions) {
            Mage::throwException(Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Please specify at least one search term.'));
        }

        return $this;
    }

    /**
     * Add data about search criteria to object state
     *
     * @param   Mage_Eav_Model_Entity_Attribute $attribute
     * @param   mixed $value
     * @return  Mage_CatalogSearch_Model_Advanced
     */
    protected function _addSearchCriteria($attribute, $value)
    {
        $name = $attribute->getStoreLabel();

        if (is_array($value)) {
            if (isset($value['from']) && isset($value['to'])) {
                if (!empty($value['from']) || !empty($value['to'])) {
                    if (isset($value['currency'])) {
                        $currencyModel = Mage::getModel('Mage_Directory_Model_Currency')->load($value['currency']);
                        $from = $currencyModel->format($value['from'], array(), false);
                        $to = $currencyModel->format($value['to'], array(), false);
                    } else {
                        $currencyModel = null;
                    }

                    if (strlen($value['from']) > 0 && strlen($value['to']) > 0) {
                        // -
                        $value = sprintf('%s - %s',
                            ($currencyModel ? $from : $value['from']), ($currencyModel ? $to : $value['to']));
                    } elseif (strlen($value['from']) > 0) {
                        // and more
                        $value = Mage::helper('Mage_CatalogSearch_Helper_Data')->__('%s and greater', ($currencyModel ? $from : $value['from']));
                    } elseif (strlen($value['to']) > 0) {
                        // to
                        $value = Mage::helper('Mage_CatalogSearch_Helper_Data')->__('up to %s', ($currencyModel ? $to : $value['to']));
                    }
                } else {
                    return $this;
                }
            }
        }

        if (($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect')
            && is_array($value)
        ) {
            foreach ($value as $key => $val){
                $value[$key] = $attribute->getSource()->getOptionText($val);

                if (is_array($value[$key])) {
                    $value[$key] = $value[$key]['label'];
                }
            }
            $value = implode(', ', $value);
        } else if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
            $value = $attribute->getSource()->getOptionText($value);
            if (is_array($value))
                $value = $value['label'];
        } else if ($attribute->getFrontendInput() == 'boolean') {
            $value = $value == 1
                ? Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Yes')
                : Mage::helper('Mage_CatalogSearch_Helper_Data')->__('No');
        }

        $this->_searchCriterias[] = array('name' => $name, 'value' => $value);
        return $this;
    }

    /**
     * Returns prepared search criterias in text
     *
     * @return array
     */
    public function getSearchCriterias()
    {
        return $this->_searchCriterias;
    }

    /**
     * Retrieve advanced search product collection
     *
     * @return Mage_CatalogSearch_Model_Resource_Advanced_Collection
     */
    public function getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $collection = $this->_engine->getAdvancedResultCollection();
            $this->prepareProductCollection($collection);
            if (!$collection) {
                return $collection;
            }
            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    /**
     * Prepare product collection
     *
     * @param Mage_CatalogSearch_Model_Resource_Advanced_Collection $collection
     * @return Mage_Catalog_Model_Layer
     */
    public function prepareProductCollection($collection)
    {
        $collection->addAttributeToSelect(Mage::getSingleton('Mage_Catalog_Model_Config')->getProductAttributes())
            ->setStore(Mage::app()->getStore())
            ->addMinimalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->setVisibility(Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInSearchIds());

        return $this;
    }
}
