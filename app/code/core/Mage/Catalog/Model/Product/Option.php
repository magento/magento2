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
 * Catalog product option model
 *
 * @method Mage_Catalog_Model_Resource_Product_Option _getResource()
 * @method Mage_Catalog_Model_Resource_Product_Option getResource()
 * @method int getProductId()
 * @method Mage_Catalog_Model_Product_Option setProductId(int $value)
 * @method string getType()
 * @method Mage_Catalog_Model_Product_Option setType(string $value)
 * @method int getIsRequire()
 * @method Mage_Catalog_Model_Product_Option setIsRequire(int $value)
 * @method string getSku()
 * @method Mage_Catalog_Model_Product_Option setSku(string $value)
 * @method int getMaxCharacters()
 * @method Mage_Catalog_Model_Product_Option setMaxCharacters(int $value)
 * @method string getFileExtension()
 * @method Mage_Catalog_Model_Product_Option setFileExtension(string $value)
 * @method int getImageSizeX()
 * @method Mage_Catalog_Model_Product_Option setImageSizeX(int $value)
 * @method int getImageSizeY()
 * @method Mage_Catalog_Model_Product_Option setImageSizeY(int $value)
 * @method int getSortOrder()
 * @method Mage_Catalog_Model_Product_Option setSortOrder(int $value)
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Option extends Mage_Core_Model_Abstract
{
    const OPTION_GROUP_TEXT   = 'text';
    const OPTION_GROUP_FILE   = 'file';
    const OPTION_GROUP_SELECT = 'select';
    const OPTION_GROUP_DATE   = 'date';

    const OPTION_TYPE_FIELD     = 'field';
    const OPTION_TYPE_AREA      = 'area';
    const OPTION_TYPE_FILE      = 'file';
    const OPTION_TYPE_DROP_DOWN = 'drop_down';
    const OPTION_TYPE_RADIO     = 'radio';
    const OPTION_TYPE_CHECKBOX  = 'checkbox';
    const OPTION_TYPE_MULTIPLE  = 'multiple';
    const OPTION_TYPE_DATE      = 'date';
    const OPTION_TYPE_DATE_TIME = 'date_time';
    const OPTION_TYPE_TIME      = 'time';

    protected $_product;

    protected $_options = array();

    protected $_valueInstance;

    protected $_values = array();

    /**
     * Translate instance
     *
     * @var Mage_Core_Model_Translate
     */
    protected $_translate;

    /**
     * Get resource instance
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _getResource()
    {
        return $this->_resource ?: parent::_getResource();
    }

    protected function _construct()
    {
        $this->_init('Mage_Catalog_Model_Resource_Product_Option');
        parent::_construct();
    }

    /**
     * Add value of option to values array
     *
     * @param Mage_Catalog_Model_Product_Option_Value $value
     * @return Mage_Catalog_Model_Product_Option
     */
    public function addValue(Mage_Catalog_Model_Product_Option_Value $value)
    {
        $this->_values[$value->getId()] = $value;
        return $this;
    }

    /**
     * Get value by given id
     *
     * @param int $valueId
     * @return Mage_Catalog_Model_Product_Option_Value
     */
    public function getValueById($valueId)
    {
        if (isset($this->_values[$valueId])) {
            return $this->_values[$valueId];
        }

        return null;
    }

    public function getValues()
    {
        return $this->_values;
    }

    /**
     * Retrieve value instance
     *
     * @return Mage_Catalog_Model_Product_Option_Value
     */
    public function getValueInstance()
    {
        if (!$this->_valueInstance) {
            $this->_valueInstance = Mage::getSingleton('Mage_Catalog_Model_Product_Option_Value');
        }
        return $this->_valueInstance;
    }

    /**
     * Add option for save it
     *
     * @param array $option
     * @return Mage_Catalog_Model_Product_Option
     */
    public function addOption($option)
    {
        $this->_options[] = $option;
        return $this;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set options for array
     *
     * @param array $options
     * @return Mage_Catalog_Model_Product_Option
     */
    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Set options to empty array
     *
     * @return Mage_Catalog_Model_Product_Option
     */
    public function unsetOptions()
    {
        $this->_options = array();
        return $this;
    }

    /**
     * Retrieve product instance
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Set product instance
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Option
     */
    public function setProduct(Mage_Catalog_Model_Product $product = null)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Get group name of option by given option type
     *
     * @param string $type
     * @return string
     */
    public function getGroupByType($type = null)
    {
        if ($type === null) {
            $type = $this->getType();
        }
        $optionGroupsToTypes = array(
            self::OPTION_TYPE_FIELD => self::OPTION_GROUP_TEXT,
            self::OPTION_TYPE_AREA => self::OPTION_GROUP_TEXT,
            self::OPTION_TYPE_FILE => self::OPTION_GROUP_FILE,
            self::OPTION_TYPE_DROP_DOWN => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_RADIO => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_CHECKBOX => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_MULTIPLE => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_DATE => self::OPTION_GROUP_DATE,
            self::OPTION_TYPE_DATE_TIME => self::OPTION_GROUP_DATE,
            self::OPTION_TYPE_TIME => self::OPTION_GROUP_DATE,
        );

        return isset($optionGroupsToTypes[$type])?$optionGroupsToTypes[$type]:'';
    }

    /**
     * Group model factory
     *
     * @param string $type Option type
     * @return Mage_Catalog_Model_Product_Option_Group_Abstract
     */
    public function groupFactory($type)
    {
        $group = $this->getGroupByType($type);
        if (!empty($group)) {
            return Mage::getModel('Mage_Catalog_Model_Product_Option_Type_' . uc_words($group));
        }
        Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Wrong option type to get group instance.'));
    }

    /**
     * Save options.
     *
     * @return Mage_Catalog_Model_Product_Option
     */
    public function saveOptions()
    {
        foreach ($this->getOptions() as $option) {
            $this->setData($option)
                ->setData('product_id', $this->getProduct()->getId())
                ->setData('store_id', $this->getProduct()->getStoreId());

            if ($this->getData('option_id') == '0') {
                $this->unsetData('option_id');
            } else {
                $this->setId($this->getData('option_id'));
            }
            $isEdit = (bool)$this->getId()? true:false;

            if ($this->getData('is_delete') == '1') {
                if ($isEdit) {
                    $this->getValueInstance()->deleteValue($this->getId());
                    $this->deletePrices($this->getId());
                    $this->deleteTitles($this->getId());
                    $this->delete();
                }
            } else {
                if ($this->getData('previous_type') != '') {
                    $previousType = $this->getData('previous_type');

                    /**
                     * if previous option has different group from one is came now
                     * need to remove all data of previous group
                     */
                    if ($this->getGroupByType($previousType) != $this->getGroupByType($this->getData('type'))) {

                        switch ($this->getGroupByType($previousType)) {
                            case self::OPTION_GROUP_SELECT:
                                $this->unsetData('values');
                                if ($isEdit) {
                                    $this->getValueInstance()->deleteValue($this->getId());
                                }
                                break;
                            case self::OPTION_GROUP_FILE:
                                $this->setData('file_extension', '');
                                $this->setData('image_size_x', '0');
                                $this->setData('image_size_y', '0');
                                break;
                            case self::OPTION_GROUP_TEXT:
                                $this->setData('max_characters', '0');
                                break;
                            case self::OPTION_GROUP_DATE:
                                break;
                        }
                        if ($this->getGroupByType($this->getData('type')) == self::OPTION_GROUP_SELECT) {
                            $this->setData('sku', '');
                            $this->unsetData('price');
                            $this->unsetData('price_type');
                            if ($isEdit) {
                                $this->deletePrices($this->getId());
                            }
                        }
                    }
                }
                $this->save();            }
        }//eof foreach()
        return $this;
    }

    protected function _afterSave()
    {
        $this->getValueInstance()->unsetValues();
        if (is_array($this->getData('values'))) {
            foreach ($this->getData('values') as $value) {
                $this->getValueInstance()->addValue($value);
            }

            $this->getValueInstance()->setOption($this)
                ->saveValues();
        } elseif ($this->getGroupByType($this->getType()) == self::OPTION_GROUP_SELECT) {
            Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Select type options required values rows.'));
        }

        return parent::_afterSave();
    }

    /**
     * Return price. If $flag is true and price is percent
     *  return converted percent to price
     *
     * @param bool $flag
     * @return decimal
     */
    public function getPrice($flag=false)
    {
        if ($flag && $this->getPriceType() == 'percent') {
            $basePrice = $this->getProduct()->getFinalPrice();
            $price = $basePrice*($this->_getData('price')/100);
            return $price;
        }
        return $this->_getData('price');
    }

    /**
     * Delete prices of option
     *
     * @param int $option_id
     * @return Mage_Catalog_Model_Product_Option
     */
    public function deletePrices($option_id)
    {
        $this->getResource()->deletePrices($option_id);
        return $this;
    }

    /**
     * Delete titles of option
     *
     * @param int $option_id
     * @return Mage_Catalog_Model_Product_Option
     */
    public function deleteTitles($option_id)
    {
        $this->getResource()->deleteTitles($option_id);
        return $this;
    }

    /**
     * get Product Option Collection
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Option_Collection
     */
    public function getProductOptionCollection(Mage_Catalog_Model_Product $product)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->addTitleToResult($product->getStoreId())
            ->addPriceToResult($product->getStoreId())
            ->setOrder('sort_order', 'asc')
            ->setOrder('title', 'asc');

        if ($this->getAddRequiredFilter()) {
            $collection->addRequiredFilter($this->getAddRequiredFilterValue());
        }

        $collection->addValuesToResult($product->getStoreId());
        return $collection;
    }

    /**
     * Get collection of values for current option
     *
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function getValuesCollection()
    {
        $collection = $this->getValueInstance()
            ->getValuesCollection($this);

        return $collection;
    }

    /**
     * Get collection of values by given option ids
     *
     * @param array $optionIds
     * @param int $store_id
     * @return unknown
     */
    public function getOptionValuesByOptionId($optionIds, $store_id)
    {
        $collection = Mage::getModel('Mage_Catalog_Model_Product_Option_Value')
            ->getValuesByOption($optionIds, $this->getId(), $store_id);

        return $collection;
    }

    /**
     * Prepare array of options for duplicate
     *
     * @return array
     */
    public function prepareOptionForDuplicate()
    {
        $this->setProductId(null);
        $this->setOptionId(null);
        $newOption = $this->__toArray();
        if ($_values = $this->getValues()) {
            $newValuesArray = array();
            foreach ($_values as $_value) {
                $newValuesArray[] = $_value->prepareValueForDuplicate();
            }
            $newOption['values'] = $newValuesArray;
        }

        return $newOption;
    }

    /**
     * Duplicate options for product
     *
     * @param int $oldProductId
     * @param int $newProductId
     * @return Mage_Catalog_Model_Product_Option
     */
    public function duplicate($oldProductId, $newProductId)
    {
        $this->getResource()->duplicate($this, $oldProductId, $newProductId);

        return $this;
    }

    /**
     * Retrieve option searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        return $this->_getResource()->getSearchableData($productId, $storeId);
    }

    /**
     * Clearing object's data
     *
     * @return Mage_Catalog_Model_Product_Option
     */
    protected function _clearData()
    {
        $this->_data = array();
        $this->_values = array();
        return $this;
    }

    /**
     * Clearing cyclic references
     *
     * @return Mage_Catalog_Model_Product_Option
     */
    protected function _clearReferences()
    {
        if (!empty($this->_values)) {
            foreach ($this->_values as $value) {
                $value->unsetOption();
            }
        }
        return $this;
    }
}
