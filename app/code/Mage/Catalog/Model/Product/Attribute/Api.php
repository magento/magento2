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
 * Catalog product attribute api
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Attribute_Api extends Mage_Catalog_Model_Api_Resource
{
    /**
     * @var Magento_Cache_FrontendInterface
     */
    private $_attributeLabelCache;

    /**
     * Product entity type id
     *
     * @var int
     */
    protected $_entityTypeId;

    /**
     * Constructor. Initializes default values.
     */
    public function __construct(Magento_Cache_FrontendInterface $attributeLabelCache)
    {
        $this->_attributeLabelCache = $attributeLabelCache;
        $this->_storeIdSessionField = 'product_store_id';
        $this->_ignoredAttributeCodes[] = 'type_id';
        $this->_ignoredAttributeTypes[] = 'gallery';
        $this->_ignoredAttributeTypes[] = 'media_image';
        $this->_entityTypeId = Mage::getModel('Mage_Eav_Model_Entity')->setType('catalog_product')->getTypeId();
    }

    /**
     * Retrieve attributes from specified attribute set
     *
     * @param int $setId
     * @return array
     */
    public function items($setId)
    {
        $attributes = Mage::getModel('Mage_Catalog_Model_Product')->getResource()
                ->loadAllAttributes()
                ->getSortedAttributes($setId);
        $result = array();

        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if ((!$attribute->getId() || $attribute->isInSet($setId))
                    && $this->_isAllowedAttribute($attribute)) {

                if (!$attribute->getId() || $attribute->isScopeGlobal()) {
                    $scope = 'global';
                } elseif ($attribute->isScopeWebsite()) {
                    $scope = 'website';
                } else {
                    $scope = 'store';
                }

                $result[] = array(
                    'attribute_id' => $attribute->getId(),
                    'code' => $attribute->getAttributeCode(),
                    'type' => $attribute->getFrontendInput(),
                    'required' => $attribute->getIsRequired(),
                    'scope' => $scope
                );
            }
        }

        return $result;
    }

    /**
     * Retrieve attribute options
     *
     * @param int $attributeId
     * @param string|int $store
     * @return array
     */
    public function options($attributeId, $store = null)
    {
        $storeId = $this->_getStoreId($store);
        $attribute = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStoreId($storeId)
                ->getResource()
                ->getAttribute($attributeId);

        /* @var $attribute Mage_Catalog_Model_Entity_Attribute */
        if (!$attribute) {
            $this->_fault('not_exists');
        }
        $options = array();
        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $optionId => $optionValue) {
                if (is_array($optionValue)) {
                    $options[] = $optionValue;
                } else {
                    $options[] = array(
                        'value' => $optionId,
                        'label' => $optionValue
                    );
                }
            }
        }

        return $options;
    }

    /**
     * Retrieve list of possible attribute types
     *
     * @return array
     */
    public function types()
    {
        return Mage::getModel('Mage_Catalog_Model_Product_Attribute_Source_Inputtype')->toOptionArray();
    }

    /**
     * Create new product attribute
     *
     * @param array $data input data
     * @return integer
     */
    public function create($data)
    {
        /** @var $model Mage_Catalog_Model_Resource_Eav_Attribute */
        $model = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute');
        /** @var $helper Mage_Catalog_Helper_Product */
        $helper = Mage::helper('Mage_Catalog_Helper_Product');

        if (empty($data['attribute_code']) || !is_array($data['frontend_label'])) {
            $this->_fault('invalid_parameters');
        }

        //validate attribute_code
        if (!preg_match('/^[a-z][a-z_0-9]{0,254}$/', $data['attribute_code'])) {
            $this->_fault('invalid_code');
        }

        //validate frontend_input
        $allowedTypes = array();
        foreach ($this->types() as $type) {
            $allowedTypes[] = $type['value'];
        }
        if (!in_array($data['frontend_input'], $allowedTypes)) {
            $this->_fault('invalid_frontend_input');
        }

        $data['source_model'] = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
        $data['backend_model'] = $helper->getAttributeBackendModelByInputType($data['frontend_input']);
        if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
            $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
        }

        $this->_prepareDataForSave($data);

        $model->addData($data);
        $model->setEntityTypeId($this->_entityTypeId);
        $model->setIsUserDefined(1);

        try {
            $model->save();
            $this->_attributeLabelCache->clean();
        } catch (Exception $e) {
            $this->_fault('unable_to_save', $e->getMessage());
        }

        return (int) $model->getId();
    }

    /**
     * Update product attribute
     *
     * @param string|integer $attribute attribute code or ID
     * @param array $data
     * @return boolean
     */
    public function update($attribute, $data)
    {
        $model = $this->_getAttribute($attribute);

        if ($model->getEntityTypeId() != $this->_entityTypeId) {
            $this->_fault('can_not_edit');
        }

        $data['attribute_code'] = $model->getAttributeCode();
        $data['is_user_defined'] = $model->getIsUserDefined();
        $data['frontend_input'] = $model->getFrontendInput();

        $this->_prepareDataForSave($data);

        $model->addData($data);
        try {
            $model->save();
            $this->_attributeLabelCache->clean();
            return true;
        } catch (Exception $e) {
            $this->_fault('unable_to_save', $e->getMessage());
        }
    }

    /**
     * Remove attribute
     *
     * @param integer|string $attribute attribute ID or code
     * @return boolean
     */
    public function remove($attribute)
    {
        $model = $this->_getAttribute($attribute);

        if ($model->getEntityTypeId() != $this->_entityTypeId) {
            $this->_fault('can_not_delete');
        }

        try {
            $model->delete();
            return true;
        } catch (Exception $e) {
            $this->_fault('can_not_delete', $e->getMessage());
        }
    }

    /**
     * Get full information about attribute with list of options
     *
     * @param integer|string $attribute attribute ID or code
     * @return array
     */
    public function info($attribute)
    {
        $model = $this->_getAttribute($attribute);

        if ($model->isScopeGlobal()) {
            $scope = 'global';
        } elseif ($model->isScopeWebsite()) {
            $scope = 'website';
        } else {
            $scope = 'store';
        }

        $frontendLabels = array(
            array(
                'store_id' => 0,
                'label' => $model->getFrontendLabel()
            )
        );
        foreach ($model->getStoreLabels() as $store_id => $label) {
            $frontendLabels[] = array(
                'store_id' => $store_id,
                'label' => $label
            );
        }

        $result = array(
            'attribute_id' => $model->getId(),
            'attribute_code' => $model->getAttributeCode(),
            'frontend_input' => $model->getFrontendInput(),
            'default_value' => $model->getDefaultValue(),
            'is_unique' => $model->getIsUnique(),
            'is_required' => $model->getIsRequired(),
            'apply_to' => $model->getApplyTo(),
            'is_configurable' => $model->getIsConfigurable(),
            'is_searchable' => $model->getIsSearchable(),
            'is_visible_in_advanced_search' => $model->getIsVisibleInAdvancedSearch(),
            'is_comparable' => $model->getIsComparable(),
            'is_used_for_promo_rules' => $model->getIsUsedForPromoRules(),
            'is_visible_on_front' => $model->getIsVisibleOnFront(),
            'used_in_product_listing' => $model->getUsedInProductListing(),
            'frontend_label' => $frontendLabels
        );
        if ($model->getFrontendInput() != 'price') {
            $result['scope'] = $scope;
        }

        // set additional fields to different types
        switch ($model->getFrontendInput()) {
            case 'text':
                    $result['additional_fields'] = array(
                        'frontend_class' => $model->getFrontendClass(),
                        'is_html_allowed_on_front' => $model->getIsHtmlAllowedOnFront(),
                        'used_for_sort_by' => $model->getUsedForSortBy()
                    );
                    break;
            case 'textarea':
                    $result['additional_fields'] = array(
                        'is_wysiwyg_enabled' => $model->getIsWysiwygEnabled(),
                        'is_html_allowed_on_front' => $model->getIsHtmlAllowedOnFront(),
                    );
                    break;
            case 'date':
            case 'boolean':
                    $result['additional_fields'] = array(
                        'used_for_sort_by' => $model->getUsedForSortBy()
                    );
                    break;
            case 'multiselect':
                    $result['additional_fields'] = array(
                        'is_filterable' => $model->getIsFilterable(),
                        'is_filterable_in_search' => $model->getIsFilterableInSearch(),
                        'position' => $model->getPosition()
                    );
                    break;
            case 'select':
            case 'price':
                    $result['additional_fields'] = array(
                        'is_filterable' => $model->getIsFilterable(),
                        'is_filterable_in_search' => $model->getIsFilterableInSearch(),
                        'position' => $model->getPosition(),
                        'used_for_sort_by' => $model->getUsedForSortBy()
                    );
                    break;
            default:
                    $result['additional_fields'] = array();
                    break;
        }

        // set options
        $options = $this->options($model->getId());
        // remove empty first element
        if ($model->getFrontendInput() != 'boolean') {
            array_shift($options);
        }

        if (count($options) > 0) {
            $result['options'] = $options;
        }

        return $result;
    }

    /**
     * Add option to select or multiselect attribute
     *
     * @param  integer|string $attribute attribute ID or code
     * @param  array $data
     * @return bool
     */
    public function addOption($attribute, $data)
    {
        $model = $this->_getAttribute($attribute);

        if (!$model->usesSource()) {
            $this->_fault('invalid_frontend_input');
        }

        /** @var $helperCatalog Mage_Catalog_Helper_Data */
        $helperCatalog = Mage::helper('Mage_Catalog_Helper_Data');

        $optionLabels = array();
        foreach ($data['label'] as $label) {
            $storeId = $label['store_id'];
            $labelText = $helperCatalog->stripTags($label['value']);
            if (is_array($storeId)) {
                foreach ($storeId as $multiStoreId) {
                    $optionLabels[$multiStoreId] = $labelText;
                }
            } else {
                $optionLabels[$storeId] = $labelText;
            }
        }
        // data in the following format is accepted by the model
        // it simulates parameters of the request made to
        // Mage_Adminhtml_Catalog_Product_AttributeController::saveAction()
        $modelData = array(
            'option' => array(
                'value' => array(
                    'option_1' => $optionLabels
                ),
                'order' => array(
                    'option_1' => (int) $data['order']
                )
            )
        );
        if ($data['is_default']) {
            $modelData['default'][] = 'option_1';
        }

        $model->addData($modelData);
        try {
            $model->save();
        } catch (Exception $e) {
            $this->_fault('unable_to_add_option', $e->getMessage());
        }

        return true;
    }

    /**
     * Remove option from select or multiselect attribute
     *
     * @param  integer|string $attribute attribute ID or code
     * @param  integer $optionId option to remove ID
     * @return bool
     */
    public function removeOption($attribute, $optionId)
    {
        $model = $this->_getAttribute($attribute);

        if (!$model->usesSource()) {
            $this->_fault('invalid_frontend_input');
        }

        // data in the following format is accepted by the model
        // it simulates parameters of the request made to
        // Mage_Adminhtml_Catalog_Product_AttributeController::saveAction()
        $modelData = array(
            'option' => array(
                'value' => array(
                    $optionId => array()
                ),
                'delete' => array(
                    $optionId => '1'
                )
            )
        );
        $model->addData($modelData);
        try {
            $model->save();
        } catch (Exception $e) {
            $this->_fault('unable_to_remove_option', $e->getMessage());
        }

        return true;
    }

    /**
     * Prepare request input data for saving
     *
     * @param array $data input data
     * @return void
     */
    protected function _prepareDataForSave(&$data)
    {
        /** @var $helperCatalog Mage_Catalog_Helper_Data */
        $helperCatalog = Mage::helper('Mage_Catalog_Helper_Data');

        if ($data['scope'] == 'global') {
            $data['is_global'] = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL;
        } else if ($data['scope'] == 'website') {
            $data['is_global'] = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE;
        } else {
            $data['is_global'] = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE;
        }
        if (!isset($data['is_configurable'])) {
            $data['is_configurable'] = 0;
        }
        if (!isset($data['is_filterable'])) {
            $data['is_filterable'] = 0;
        }
        if (!isset($data['is_filterable_in_search'])) {
            $data['is_filterable_in_search'] = 0;
        }
        if (!isset($data['apply_to'])) {
            $data['apply_to'] = array();
        }
        // set frontend labels array with store_id as keys
        if (isset($data['frontend_label']) && is_array($data['frontend_label'])) {
            $labels = array();
            foreach ($data['frontend_label'] as $label) {
                $storeId = $label['store_id'];
                $labelText = $helperCatalog->stripTags($label['label']);
                $labels[$storeId] = $labelText;
            }
            $data['frontend_label'] = $labels;
        }
        // set additional fields
        if (isset($data['additional_fields']) && is_array($data['additional_fields'])) {
            $data = array_merge($data, $data['additional_fields']);
            unset($data['additional_fields']);
        }
        //default value
        if (!empty($data['default_value'])) {
            $data['default_value'] = $helperCatalog->stripTags($data['default_value']);
        }
    }

    /**
     * Load model by attribute ID or code
     *
     * @param integer|string $attribute
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _getAttribute($attribute)
    {
        $model = Mage::getResourceModel('Mage_Catalog_Model_Resource_Eav_Attribute')
            ->setEntityTypeId($this->_entityTypeId);

        if (is_numeric($attribute)) {
            $model->load(intval($attribute));
        } else {
            $model->load($attribute, 'attribute_code');
        }

        if (!$model->getId()) {
            $this->_fault('not_exists');
        }

        return $model;
    }

} // Class Mage_Catalog_Model_Product_Attribute_Api End
