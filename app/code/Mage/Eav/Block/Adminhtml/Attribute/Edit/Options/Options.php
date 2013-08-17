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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Attribute add/edit form options tab
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Options extends Mage_Backend_Block_Template
{
    /** @var Mage_Core_Model_StoreManager */
    protected $_storeManager;

    /** @var Mage_Core_Model_Registry */
    protected $_registry;

    /**
     * @inheritdoc
     */
    protected $_template = 'Mage_Adminhtml::catalog/product/attribute/options.phtml';

    /**
     * @inheritdoc
     *
     * @param Mage_Core_Block_Template_Context $context
     * @param Mage_Core_Model_StoreManager $storeManager
     * @param Mage_Core_Model_Registry $registry
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_StoreManager $storeManager,
        Mage_Core_Model_Registry $registry,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Is true only for system attributes which use source model
     * Option labels and position for such attributes are kept in source model and thus cannot be overridden
     *
     * @return bool
     */
    public function canManageOptionDefaultOnly()
    {
        $attribute = $this->getAttributeObject();
        return !$attribute->getCanManageOptionLabels() && !$attribute->getIsUserDefined()
            && $attribute->getSourceModel();
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return array
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores(true));
        }
        return $this->_getData('stores');
    }

    /**
     * Retrieve attribute option values if attribute input type select or multiselect
     *
     * @return array
     */
    public function getOptionValues()
    {
        $values = $this->_getData('option_values');
        if ($values === null) {
            $values = array();

            $attribute = $this->getAttributeObject();
            $optionCollection = $this->_getOptionValuesCollection($attribute);
            if ($optionCollection) {
                $values = $this->_prepareOptionValues($attribute, $optionCollection);
            }

            $this->setData('option_values', $values);
        }

        return $values;
    }

    /**
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param array|Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection $optionCollection
     * @return array
     */
    protected function _prepareOptionValues(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $optionCollection)
    {
        $type = $attribute->getFrontendInput();
        if ($type === 'select' || $type === 'multiselect') {
            $defaultValues = explode(',', $attribute->getDefaultValue());
            $inputType = $type === 'select' ? 'radio' : 'checkbox';
        } else {
            $defaultValues = array();
            $inputType = '';
        }

        $values = array();
        $isSystemAttribute = is_array($optionCollection);
        foreach ($optionCollection as $option) {
            $bunch = $isSystemAttribute
                ? $this->_prepareSystemAttributeOptionValues($option, $inputType, $defaultValues)
                : $this->_prepareUserDefinedAttributeOptionValues($option, $inputType, $defaultValues);
            foreach ($bunch as $value) {
                $values[] = new Varien_Object($value);
            }
        }

        return $values;
    }

    /**
     * Retrieve option values collection
     * It is represented by an array in case of system attribute
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return array|Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection
     */
    protected function _getOptionValuesCollection(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        if ($this->canManageOptionDefaultOnly()) {
            $options = Mage::getModel($attribute->getSourceModel())
                ->setAttribute($attribute)
                ->getAllOptions();
            return $options;
        } else {
            return Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection')
                ->setAttributeFilter($attribute->getId())
                ->setPositionOrder('asc', true)
                ->load();
        }
    }

    /**
     * Prepare option values of system attribute
     *
     * @param array|Mage_Eav_Model_Resource_Entity_Attribute_Option $option
     * @param string $inputType
     * @param array $defaultValues
     * @param string $valuePrefix
     * @return array
     */
    protected function _prepareSystemAttributeOptionValues($option, $inputType, $defaultValues, $valuePrefix = '')
    {
        if (is_array($option['value'])) {
            $values = array();
            foreach ($option['value'] as $subOption) {
                $bunch = $this->_prepareSystemAttributeOptionValues(
                    $subOption, $inputType, $defaultValues, $option['label'] . ' / '
                );
                $values[] = $bunch[0];
            }
            return $values;
        }

        $value['checked'] = in_array($option['value'], $defaultValues) ? 'checked="checked"' : '';
        $value['intype'] = $inputType;
        $value['id'] = $option['value'];
        $value['sort_order'] = 0;

        foreach ($this->getStores() as $store) {
            $storeId = $store->getId();
            $value['store' . $storeId] = $storeId == Mage_Core_Model_AppInterface::ADMIN_STORE_ID
                ? $valuePrefix . $this->escapeHtml($option['label'])
                : '';
        }

        return array($value);
    }

    /**
     * Prepare option values of user defined attribute
     *
     * @param array|Mage_Eav_Model_Resource_Entity_Attribute_Option $option
     * @param string $inputType
     * @param array $defaultValues
     * @return array
     */
    protected function _prepareUserDefinedAttributeOptionValues($option, $inputType, $defaultValues)
    {
        $optionId = $option->getId();

        $value['checked'] = in_array($optionId, $defaultValues) ? 'checked="checked"' : '';
        $value['intype'] = $inputType;
        $value['id'] = $optionId;
        $value['sort_order'] = $option->getSortOrder();

        foreach ($this->getStores() as $store) {
            $storeId = $store->getId();
            $storeValues = $this->getStoreOptionValues($storeId);
            $value['store' . $storeId] = isset($storeValues[$optionId])
                ? $this->escapeHtml($storeValues[$optionId])
                : '';
        }

        return array($value);
    }

    /**
     * Retrieve attribute option values for given store id
     *
     * @param integer $storeId
     * @return array
     */
    public function getStoreOptionValues($storeId)
    {
        $values = $this->getData('store_option_values_'.$storeId);
        if (is_null($values)) {
            $values = array();
            $valuesCollection = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection')
                ->setAttributeFilter($this->getAttributeObject()->getId())
                ->setStoreFilter($storeId, false)
                ->load();
            foreach ($valuesCollection as $item) {
                $values[$item->getId()] = $item->getValue();
            }
            $this->setData('store_option_values_'.$storeId, $values);
        }
        return $values;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    private function getAttributeObject()
    {
        return $this->_registry->registry('entity_attribute');
    }
}
