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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product options api
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Option_Api extends Mage_Catalog_Model_Api_Resource
{

    /**
     * Add custom option to product
     *
     * @param string $productId
     * @param array $data
     * @param int|string|null $store
     * @return bool $isAdded
     */
    public function add($productId, $data, $store = null)
    {
        $product = $this->_getProduct($productId, $store, null);
        if (!(is_array($data['additional_fields']) and count($data['additional_fields']))) {
            $this->_fault('invalid_data');
        }
        if (!$this->_isTypeAllowed($data['type'])) {
            $this->_fault('invalid_type');
        }
        $this->_prepareAdditionalFields(
            $data,
            $product->getOptionInstance()->getGroupByType($data['type'])
        );
        $this->_saveProductCustomOption($product, $data);
        return true;
    }

    /**
     * Update product custom option data
     *
     * @param string $optionId
     * @param array $data
     * @param int|string|null $store
     * @return bool
     */
    public function update($optionId, $data, $store = null)
    {
        /** @var $option Mage_Catalog_Model_Product_Option */
        $option = Mage::getModel('Mage_Catalog_Model_Product_Option')->load($optionId);
        if (!$option->getId()) {
            $this->_fault('option_not_exists');
        }
        $product = $this->_getProduct($option->getProductId(), $store, null);
        $option = $product->getOptionById($optionId);
        if (isset($data['type']) and !$this->_isTypeAllowed($data['type'])) {
            $this->_fault('invalid_type');
        }
        if (isset($data['additional_fields'])) {
            $this->_prepareAdditionalFields(
                $data,
                $option->getGroupByType()
            );
        }
        foreach ($option->getValues() as $valueId => $value) {
            if(isset($data['values'][$valueId])) {
                $data['values'][$valueId] = array_merge($value->getData(), $data['values'][$valueId]);
            }
        }
        $data = array_merge($option->getData(), $data);
        $this->_saveProductCustomOption($product, $data);
        return true;
    }

    /**
     * Prepare custom option data for saving by model. Used for custom option add and update
     *
     * @param array $data
     * @param string $groupType
     * @return void
     */
    protected function _prepareAdditionalFields(&$data, $groupType)
    {
        if (is_array($data['additional_fields'])) {
            if ($groupType != Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                // reset can be used as there should be the only
                // element in 'additional_fields' for options of all types except those from Select group
                $field = reset($data['additional_fields']);
                if (!(is_array($field) and count($field))) {
                    $this->_fault('invalid_data');
                } else {
                    foreach ($field as $key => $value) {
                        $data[$key] = $value;
                    }
                }
            } else {
                // convert Select rows array to appropriate format for saving in the model
                foreach ($data['additional_fields'] as $row) {
                    if (!(is_array($row) and count($row))) {
                        $this->_fault('invalid_data');
                    } else {
                        foreach ($row as $key => $value) {
                            $row[$key] = Mage::helper('Mage_Catalog_Helper_Data')->stripTags($value);
                        }
                        if (!empty($row['value_id'])) {
                            // map 'value_id' to 'option_type_id'
                            $row['option_type_id'] = $row['value_id'];
                            unset($row['value_id']);
                            $data['values'][$row['option_type_id']] = $row;
                        } else {
                            $data['values'][] = $row;
                        }
                    }
                }
            }
        }
        unset($data['additional_fields']);
    }

    /**
     * Save product custom option data. Used for custom option add and update.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     * @return void
     */
    protected function _saveProductCustomOption($product, $data)
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = Mage::helper('Mage_Catalog_Helper_Data')->stripTags($value);
            }
        }
        // setProductOptions expects data to be an array of options arrays
        $data = array($data);
        if (!$product->getOptionsReadonly()) {
            $product->setProductOptions($data);
        }
        $product->setCanSaveCustomOptions(!$product->getOptionsReadonly());
        try {
            // an empty request can be set as event parameter
            // because it is not used for options changing in observers
            Mage::dispatchEvent(
                'catalog_product_prepare_save',
                array('product' => $product, 'request' => new Mage_Core_Controller_Request_Http())
            );
            $product->save();
        } catch (Exception $e) {
            $this->_fault('save_option_error', $e->getMessage());
        }
    }

    /**
     * Read list of possible custom option types from module config
     *
     * @return array
     */
    public function types()
    {
        $path = Mage_Adminhtml_Model_System_Config_Source_Product_Options_Type::PRODUCT_OPTIONS_GROUPS_PATH;
        $types = array();
        foreach (Mage::getConfig()->getNode($path)->children() as $group) {
            $groupTypes = Mage::getConfig()->getNode($path . '/' . $group->getName() . '/types')->children();
            /** @var $type Mage_Core_Model_Config_Element */
            foreach($groupTypes as $type){
                $labelPath = $path . '/' . $group->getName() . '/types/' . $type->getName() . '/label';
                $types[] = array(
                    'label' => (string) Mage::getConfig()->getNode($labelPath),
                    'value' => $type->getName()
                );
            }
        }
        return $types;
    }

    /**
     * Get full information about custom option in product
     *
     * @param int|string $optionId
     * @param  int|string|null $store
     * @return array
     */
    public function info($optionId, $store = null)
    {
        /** @var $option Mage_Catalog_Model_Product_Option */
        $option = Mage::getModel('Mage_Catalog_Model_Product_Option')->load($optionId);
        if (!$option->getId()) {
            $this->_fault('option_not_exists');
        }
        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->_getProduct($option->getProductId(), $store, null);
        $option = $product->getOptionById($optionId);
        $result = array(
            'title' => $option->getTitle(),
            'type' => $option->getType(),
            'is_require' => $option->getIsRequire(),
            'sort_order' => $option->getSortOrder(),
            // additional_fields should be two-dimensional array for all option types
            'additional_fields' => array(
                array(
                    'price' => $option->getPrice(),
                    'price_type' => $option->getPriceType(),
                    'sku' => $option->getSku()
                )
            )
        );
        // Set additional fields to each type group
        switch ($option->getGroupByType()) {
            case Mage_Catalog_Model_Product_Option::OPTION_GROUP_TEXT:
                $result['additional_fields'][0]['max_characters'] = $option->getMaxCharacters();
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_GROUP_FILE:
                $result['additional_fields'][0]['file_extension'] = $option->getFileExtension();
                $result['additional_fields'][0]['image_size_x'] = $option->getImageSizeX();
                $result['additional_fields'][0]['image_size_y'] = $option->getImageSizeY();
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT:
                $result['additional_fields'] = array();
                foreach ($option->getValuesCollection() as $value) {
                    $result['additional_fields'][] = array(
                        'value_id' => $value->getId(),
                        'title' => $value->getTitle(),
                        'price' => $value->getPrice(),
                        'price_type' => $value->getPriceType(),
                        'sku' => $value->getSku(),
                        'sort_order' => $value->getSortOrder()
                    );
                }
                break;
            default:
                break;
        }

        return $result;
    }

    /**
     * Retrieve list of product custom options
     *
     * @param  string $productId
     * @param  int|string|null $store
     * @return array
     */
    public function items($productId, $store = null)
    {
        $result = array();
        $product = $this->_getProduct($productId, $store, null);
        /** @var $option Mage_Catalog_Model_Product_Option */
        foreach ($product->getProductOptionsCollection() as $option) {
            $result[] = array(
                'option_id' => $option->getId(),
                'title' => $option->getTitle(),
                'type' => $option->getType(),
                'is_require' => $option->getIsRequire(),
                'sort_order' => $option->getSortOrder()
            );
        }
        return $result;
    }

    /**
     * Remove product custom option
     *
     * @param string $optionId
     * @return boolean
     */
    public function remove($optionId)
    {
        /** @var $option Mage_Catalog_Model_Product_Option */
        $option = Mage::getModel('Mage_Catalog_Model_Product_Option')->load($optionId);
        if (!$option->getId()) {
            $this->_fault('option_not_exists');
        }
        try {
            $option->getValueInstance()->deleteValue($optionId);
            $option->deletePrices($optionId);
            $option->deleteTitles($optionId);
            $option->delete();
        } catch (Exception $e){
            $this->fault('delete_option_error');
        }
        return true;
    }

    /**
     * Check is type in allowed set
     *
     * @param string $type
     * @return bool
     */
    protected function _isTypeAllowed($type)
    {
        $allowedTypes = array();
        foreach($this->types() as $optionType){
            $allowedTypes[] = $optionType['value'];
        }

        if (!in_array($type, $allowedTypes)) {
            return false;
        }
        return true;
    }

}
