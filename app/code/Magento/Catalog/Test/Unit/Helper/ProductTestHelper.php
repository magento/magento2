<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

/**
 * Test helper class for Catalog Product with custom methods
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class ProductTestHelper extends Product
{
    /**
     * @var array
     */
    private $data = [];

    /** @var DataObject|null */
    private $urlDataObject;

    /**
     * @var bool
     */
    private $setBundleOptionsDataCalled = false;
    /** @var array */
    private $setBundleOptionsDataParams = [];
    /** @var bool */
    private $setBundleSelectionsDataCalled = false;
    /** @var array */
    private $setBundleSelectionsDataParams = [];
    /** @var bool */
    private $setCanSaveCustomOptionsCalled = false;
    /** @var array */
    private $setCanSaveCustomOptionsParams = [];
    /** @var bool */
    private $setCanSaveBundleSelectionsCalled = false;
    /** @var array */
    private $setCanSaveBundleSelectionsParams = [];
    /** @var bool */
    private $setOptionsCalled = false;
    /** @var array */
    private $setOptionsParams = [];

    /**
     * Skip parent constructor to avoid dependencies
     */
    public function __construct(
        ?DataObject $urlDataObject = null
    ) {
        $this->urlDataObject = $urlDataObject;
        $this->_data = [];
    }

    /**
     * Set resource model for testing
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @return self
     */
    public function setResource($resource): self
    {
        $this->_resource = $resource;
        return $this;
    }

    /**
     * Set locked attribute for testing
     *
     * @param string $attributeCode
     * @param bool $locked
     * @return $this
     */
    public function setLockedAttribute($attributeCode, $locked = true)
    {
        if (!is_array($this->_lockedAttributes)) {
            $this->_lockedAttributes = [];
        }
        if ($locked) {
            $this->_lockedAttributes[$attributeCode] = true;
        } else {
            unset($this->_lockedAttributes[$attributeCode]);
        }
        return $this;
    }

    /**
     * Get attribute set id for testing
     *
     * @return int|null
     */
    public function getAttributeSetId()
    {
        return $this->getData('attribute_set_id');
    }

    /**
     * Set attribute set id for testing
     *
     * @param int $attributeSetId
     * @return $this
     */
    public function setAttributeSetId($attributeSetId)
    {
        return $this->setData('attribute_set_id', $attributeSetId);
    }

    /**
     * Override getCustomAttributesCodes to avoid null pointer on filterCustomAttribute
     *
     * @return array
     */
    protected function getCustomAttributesCodes()
    {
        // Return empty array to avoid dependency on filterCustomAttribute
        return [];
    }

    /**
     * Override getId to work without constructor
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->data['id'] ?? null;
    }

    /**
     * Override setId to work without constructor
     *
     * @param mixed $value
     * @return self
     */
    public function setId($value): self
    {
        $this->data['id'] = $value;
        return $this;
    }

    /**
     * Override getEntityId to work without constructor
     *
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->data['entity_id'];
    }

    /**
     * Set entity ID for testing
     *
     * @param mixed $value
     * @return self
     */
    public function setEntityId($value): self
    {
        $this->data['entity_id'] = $value;
        return $this;
    }

    /**
     * Custom getPriceType method for testing (used in Bundle products)
     *
     * @return mixed
     */
    public function getPriceType()
    {
        return $this->data['price_type'] ?? null;
    }

    /**
     * Set price type for testing
     *
     * @param mixed $priceType
     * @return self
     */
    public function setPriceType($priceType): self
    {
        $this->data['price_type'] = $priceType;
        return $this;
    }

    /**
     * Custom hasPreconfiguredValues method for testing
     *
     * @return bool
     */
    public function hasPreconfiguredValues(): bool
    {
        return $this->data['has_preconfigured_values'] ?? false;
    }

    /**
     * Set has preconfigured values for testing
     *
     * @param bool $hasValues
     * @return self
     */
    public function setHasPreconfiguredValues(bool $hasValues): self
    {
        $this->data['has_preconfigured_values'] = $hasValues;
        return $this;
    }

    /**
     * Override getTypeInstance for testing
     *
     * @return mixed
     */
    public function getTypeInstance()
    {
        // Return from internal data array since parent's getTypeInstance()
        // relies on _catalogProductType which is null when constructor is skipped
        return $this->data['type_instance'] ?? new class {
            public function getSetAttributes($product)
            {
                return [];
            }
        };
    }

    /**
     * Set type instance for testing
     *
     * @param mixed $typeInstance
     * @return self
     */
    public function setTypeInstance($typeInstance): self
    {
        $this->data['type_instance'] = $typeInstance;
        return $this;
    }

    /**
     * Get attributes for testing
     *
     * @param int|null $groupId
     * @param bool $skipSuper
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAttributes($groupId = null, $skipSuper = false)
    {
        return $this->data['attributes'] ?? [];
    }

    /**
     * Set attributes for testing
     *
     * @param array $attributes
     * @return self
     */
    public function setAttributes(array $attributes): self
    {
        $this->data['attributes'] = $attributes;
        return $this;
    }

    /**
     * Set store filter for testing (noop)
     *
     * @param mixed $storeFilter
     * @return self
     */
    public function setStoreFilter($storeFilter): self
    {
        $this->data['store_filter'] = $storeFilter;
        return $this;
    }

    /**
     * Override getPriceInfo for testing
     *
     * @return mixed
     */
    public function getPriceInfo()
    {
        return $this->data['price_info'] ?? null;
    }

    /**
     * Set price info for testing
     *
     * @param mixed $priceInfo
     * @return self
     */
    public function setPriceInfo($priceInfo): self
    {
        $this->data['price_info'] = $priceInfo;
        return $this;
    }

    /**
     * Get price
     *
     * @return mixed
     */
    public function getPrice()
    {
        return $this->data['price'] ?? null;
    }

    /**
     * Set price
     *
     * @param mixed $price
     * @return $this
     */
    public function setPrice($price): self
    {
        $this->data['price'] = $price;
        return $this;
    }

    /**
     * Override getStoreId for testing
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->data['store_id'] ?? null;
    }

    /**
     * Set store ID for testing
     *
     * @param mixed $storeId
     * @return self
     */
    public function setStoreId($storeId): self
    {
        $this->data['store_id'] = $storeId;
        return $this;
    }

    /**
     * Custom getStore method for testing
     *
     * @return mixed
     */
    public function getStore()
    {
        return $this->data['store'] ?? null;
    }

    /**
     * Set store for testing
     *
     * @param mixed $store
     * @return self
     */
    public function setStore($store)
    {
        $this->data['store'] = $store;
        return $this;
    }

    /**
     * Override getPreconfiguredValues for testing
     *
     * @return mixed
     */
    public function getPreconfiguredValues()
    {
        return $this->data['preconfigured_values'] ?? null;
    }

    /**
     * Set preconfigured values for testing
     *
     * @param mixed $values
     * @return self
     */
    public function setPreconfiguredValues($values): self
    {
        $this->data['preconfigured_values'] = $values;
        return $this;
    }

    /**
     * Custom getSelectionId method for Bundle testing
     *
     * @return mixed
     */
    public function getSelectionId()
    {
        return $this->data['selection_id'] ?? null;
    }

    /**
     * Set selection ID for testing
     *
     * @param int|null $id
     * @return self
     */
    public function setSelectionId(?int $id): self
    {
        $this->data['selection_id'] = $id;
        return $this;
    }

    /**
     * Custom getSelectionQty method for Bundle testing
     *
     * @return float|null
     */
    public function getSelectionQty(): ?float
    {
        return $this->data['selection_qty'] ?? null;
    }

    /**
     * Set selection quantity for testing
     *
     * @param float|null $qty
     * @return self
     */
    public function setSelectionQty(?float $qty): self
    {
        $this->data['selection_qty'] = $qty;
        return $this;
    }

    /**
     * Custom getSelectionCanChangeQty method for Bundle testing
     *
     * @return mixed
     */
    public function getSelectionCanChangeQty()
    {
        return $this->data['selection_can_change_qty'] ?? false;
    }

    /**
     * Set selection can change quantity for testing
     *
     * @param mixed $canChange
     * @return self
     */
    public function setSelectionCanChangeQty($canChange): self
    {
        $this->data['selection_can_change_qty'] = $canChange;
        return $this;
    }

    /**
     * Custom getIsDefault method for Bundle testing
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDefault()
    {
        return $this->data['is_default'] ?? false;
    }

    /**
     * Set is default for testing
     *
     * @param bool $isDefault
     * @return self
     */
    public function setIsDefault(bool $isDefault): self
    {
        $this->data['is_default'] = $isDefault;
        return $this;
    }

    /**
     * Override getName for testing
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    /**
     * Set name for testing
     *
     * @param mixed $name
     * @return self
     */
    public function setName($name): self
    {
        $this->data['name'] = $name;
        return $this;
    }

    /**
     * Override isSalable for testing
     *
     * @return bool
     */
    public function isSalable(): bool
    {
        return $this->data['is_salable'] ?? true;
    }

    /**
     * Set is salable for testing
     *
     * @param bool $isSalable
     * @return self
     */
    public function setIsSalable(bool $isSalable): self
    {
        $this->data['is_salable'] = $isSalable;
        return $this;
    }

    /**
     * Custom getOptionId method for Bundle testing
     *
     * @return mixed
     */
    public function getOptionId()
    {
        return $this->data['option_id'] ?? null;
    }

    /**
     * Set option ID for testing
     *
     * @param int|null $optionId
     * @return self
     */
    public function setOptionId(?int $optionId): self
    {
        $this->data['option_id'] = $optionId;
        return $this;
    }

    /**
     * Custom getTypeId method for testing
     *
     * @return string|null
     */
    public function getTypeId(): ?string
    {
        return $this->data['type_id'] ?? null;
    }

    /**
     * Set type ID for testing
     *
     * @param mixed $typeId
     * @return self
     */
    public function setTypeId($typeId): self
    {
        $this->data['type_id'] = $typeId;
        return $this;
    }

    /**
     * Custom getData method for testing
     *
     * @param string $key
     * @param mixed $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        // Check if there's a callback set for getData
        if (isset($this->data['get_data_callback'])) {
            return call_user_func($this->data['get_data_callback'], $key);
        }

        // Use separate productData array for getData/setData to avoid conflicts
        if ($key === '' || $key === null) {
            return $this->data['product_data'] ?? [];
        }
        $productData = $this->data['product_data'] ?? [];
        return $productData[$key] ?? $index;
    }

    /**
     * Set data for testing
     *
     * @param string|array $key
     * @param mixed $value
     * @return self
     */
    public function setData($key, $value = null): self
    {
        // Use separate productData array for getData/setData to avoid conflicts
        if (!isset($this->data['product_data'])) {
            $this->data['product_data'] = [];
        }

        if (is_array($key)) {
            $this->data['product_data'] = array_merge($this->data['product_data'], $key);
        } else {
            $this->data['product_data'][$key] = $value;
        }
        return $this;
    }

    /**
     * Custom getSku method for testing
     *
     * @return mixed
     */
    public function getSku()
    {
        return $this->data['sku'] ?? null;
    }

    /**
     * Set SKU for testing
     *
     * @param mixed $sku
     * @return self
     */
    public function setSku($sku): self
    {
        $this->data['sku'] = $sku;
        return $this;
    }

    /**
     * Custom setIsRelationsChanged method for testing
     *
     * @param bool $isChanged
     * @return self
     */
    public function setIsRelationsChanged(bool $isChanged): self
    {
        $this->data['is_relations_changed'] = $isChanged;
        return $this;
    }

    /**
     * Get is relations changed for testing
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRelationsChanged()
    {
        return $this->data['is_relations_changed'] ?? false;
    }

    /**
     * Custom getWebsiteId method for testing
     *
     * @return mixed
     */
    public function getWebsiteId()
    {
        return $this->data['website_id'] ?? null;
    }

    /**
     * Set website ID for testing
     *
     * @param mixed $websiteId
     * @return self
     */
    public function setWebsiteId($websiteId): self
    {
        $this->data['website_id'] = $websiteId;
        return $this;
    }

    /**
     * Custom hasCustomerGroupId method for testing
     *
     * @return bool
     */
    public function hasCustomerGroupId(): bool
    {
        return isset($this->data['customer_group_id']);
    }

    /**
     * Custom getCustomerGroupId method for testing
     *
     * @return mixed
     */
    public function getCustomerGroupId()
    {
        return $this->data['customer_group_id'] ?? null;
    }

    /**
     * Set customer group ID for testing
     *
     * @param mixed $customerGroupId
     * @return self
     */
    public function setCustomerGroupId($customerGroupId): self
    {
        $this->data['customer_group_id'] = $customerGroupId;
        return $this;
    }

    /**
     * Custom getPriceModel method for testing
     *
     * @return mixed
     */
    public function getPriceModel()
    {
        return $this->data['price_model'] ?? null;
    }

    /**
     * Set price model for testing
     *
     * @param mixed $priceModel
     * @return self
     */
    public function setPriceModel($priceModel): self
    {
        $this->data['price_model'] = $priceModel;
        return $this;
    }

    /**
     * Custom getSelectionPriceType method for testing
     *
     * @return mixed
     */
    public function getSelectionPriceType()
    {
        return $this->data['selection_price_type'] ?? null;
    }

    /**
     * Set selection price type for testing
     *
     * @param mixed $priceType
     * @return self
     */
    public function setSelectionPriceType($priceType): self
    {
        $this->data['selection_price_type'] = $priceType;
        return $this;
    }

    /**
     * Custom getSelectionPriceValue method for testing
     *
     * @return mixed
     */
    public function getSelectionPriceValue()
    {
        return $this->data['selection_price_value'] ?? null;
    }

    /**
     * Set selection price value for testing
     *
     * @param mixed $priceValue
     * @return self
     */
    public function setSelectionPriceValue($priceValue): self
    {
        $this->data['selection_price_value'] = $priceValue;
        return $this;
    }

    /**
     * Custom getExtensionAttributes method for testing
     *
     * @return mixed
     */
    public function getExtensionAttributes()
    {
        return $this->data['extension_attributes'] ?? null;
    }

    /**
     * Set extension attributes for testing
     *
     * @param mixed $extensionAttributes
     * @return self
     */
    public function setExtensionAttributes($extensionAttributes): self
    {
        $this->data['extension_attributes'] = $extensionAttributes;
        return $this;
    }

    /**
     * Set options for testing with call tracking
     *
     * @param ?array $options
     * @return self
     */
    public function setOptions(?array $options = null): self
    {
        $this->data['options'] = $options;
        if (!isset($this->data['product_data'])) {
            $this->data['product_data'] = [];
        }
        $this->data['product_data']['options'] = $options;
        $this->setOptionsCalled = true;
        $this->setOptionsParams = $options;
        return $this;
    }

    /**
     * Get options for testing
     *
     * @return array
     */
    public function getOptions()
    {
        $options = $this->data['options'] ?? ($this->data['product_data']['options'] ?? []);
        return is_array($options) ? $options : [];
    }

    /**
     * Custom getCustomOption method for testing
     *
     * @param mixed $code
     * @return mixed
     */
    public function getCustomOption($code = null)
    {
        // Check if a single custom option is set (for setCustomOption)
        if (isset($this->data['custom_option'])) {
            return $this->data['custom_option'];
        }

        if ($code === null) {
            return $this->data['custom_options'] ?? [];
        }
        return $this->data['custom_options'][$code] ?? null;
    }

    /**
     * Custom getHasOptions method for testing
     *
     * @return mixed
     */
    public function getHasOptions()
    {
        return $this->data['has_options'] ?? false;
    }

    /**
     * Custom setCartQty method for testing
     *
     * @param mixed $qty
     * @return self
     */
    public function setCartQty($qty): self
    {
        $this->data['cart_qty'] = $qty;
        return $this;
    }

    /**
     * Custom getSkipCheckRequiredOption method for testing
     *
     * @return mixed
     */
    public function getSkipCheckRequiredOption()
    {
        return $this->data['skip_check_required_option'] ?? false;
    }

    /**
     * Custom hasData method for testing
     *
     * @param string $key
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function hasData($key = '')
    {
        return $this->data['has_data'] ?? true;
    }

    /**
     * Set custom option for testing
     * Supports both single option and key-value pair
     *
     * @param mixed $optionOrKey - Either the option object or the option key
     * @param mixed $value - Optional value when using key-value pair
     * @return self
     */
    public function setCustomOption($optionOrKey, $value = null): self
    {
        // If value is provided, this is a key-value pair
        if ($value !== null) {
            $this->data['custom_options'][$optionOrKey] = $value;
        } else {
            // Single option (backward compatibility)
            $this->data['custom_option'] = $optionOrKey;
        }
        return $this;
    }

    /**
     * Custom isSaleable method for Bundle testing
     *
     * @return bool
     */
    public function isSaleable(): bool
    {
        return $this->data['is_saleable'] ?? true;
    }

    /**
     * Set isSaleable for testing
     *
     * @param bool $isSaleable
     * @return self
     */
    public function setIsSaleable(bool $isSaleable): self
    {
        $this->data['is_saleable'] = $isSaleable;
        return $this;
    }

    /**
     * Get option for testing
     *
     * @return mixed
     */
    public function getOption()
    {
        return $this->data['option'] ?? null;
    }

    /**
     * Set option for testing
     *
     * @param mixed $option
     * @return self
     */
    public function setOption($option): self
    {
        $this->data['option'] = $option;
        return $this;
    }

    /**
     * Get position for testing
     *
     * @return mixed
     */
    public function getPosition()
    {
        return $this->data['position'] ?? null;
    }

    /**
     * Set position for testing
     *
     * @param mixed $position
     * @return self
     */
    public function setPosition($position): self
    {
        $this->data['position'] = $position;
        return $this;
    }

    /**
     * Set bundle options data with call tracking
     *
     * @param mixed $data
     * @return self
     */
    public function setBundleOptionsData($data): self
    {
        $this->data['bundle_options_data'] = $data;
        $this->setBundleOptionsDataCalled = true;
        $this->setBundleOptionsDataParams = $data;
        return $this;
    }

    /**
     * Set bundle selections data with call tracking
     *
     * @param mixed $data
     * @return self
     */
    public function setBundleSelectionsData($data): self
    {
        $this->data['bundle_selections_data'] = $data;
        $this->setBundleSelectionsDataCalled = true;
        $this->setBundleSelectionsDataParams = $data;
        return $this;
    }

    /**
     * Set can save custom options with call tracking
     *
     * @param mixed $value
     * @return self
     */
    public function setCanSaveCustomOptions($value): self
    {
        $this->data['can_save_custom_options'] = $value;
        $this->setCanSaveCustomOptionsCalled = true;
        $this->setCanSaveCustomOptionsParams = $value;
        return $this;
    }

    /**
     * Set can save bundle selections with call tracking
     *
     * @param mixed $value
     * @return self
     */
    public function setCanSaveBundleSelections($value): self
    {
        $this->data['can_save_bundle_selections'] = $value;
        $this->setCanSaveBundleSelectionsCalled = true;
        $this->setCanSaveBundleSelectionsParams = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function wasSetBundleOptionsDataCalled(): bool
    {
        return $this->setBundleOptionsDataCalled;
    }

    /**
     * @return array
     */
    public function getSetBundleOptionsDataParams()
    {
        return $this->setBundleOptionsDataParams;
    }

    /**
     * @return bool
     */
    public function wasSetBundleSelectionsDataCalled(): bool
    {
        return $this->setBundleSelectionsDataCalled;
    }

    /**
     * @return array
     */
    public function getSetBundleSelectionsDataParams()
    {
        return $this->setBundleSelectionsDataParams;
    }

    /**
     * @return bool
     */
    public function wasSetCanSaveCustomOptionsCalled(): bool
    {
        return $this->setCanSaveCustomOptionsCalled;
    }

    /**
     * @return array
     */
    public function getSetCanSaveCustomOptionsParams()
    {
        return $this->setCanSaveCustomOptionsParams;
    }

    /**
     * @return bool
     */
    public function wasSetCanSaveBundleSelectionsCalled(): bool
    {
        return $this->setCanSaveBundleSelectionsCalled;
    }

    /**
     * @return array
     */
    public function getSetCanSaveBundleSelectionsParams()
    {
        return $this->setCanSaveBundleSelectionsParams;
    }

    /**
     * @return bool
     */
    public function wasSetOptionsCalled(): bool
    {
        return $this->setOptionsCalled;
    }

    /**
     * @return array
     */
    public function getSetOptionsParams()
    {
        return $this->setOptionsParams;
    }

    /**
     * Custom method for ConfigurableProduct tests
     *
     * @param string|null $key
     * @return array
     */
    public function getMediaGallery($key = null)
    {
        return $this->data['media_gallery'][$key] ?? $this->data['media_gallery'] ?? [];
    }

    /**
     * Custom method for ConfigurableProduct variation tests
     *
     * @return mixed
     */
    public function getNewVariationsAttributeSetId()
    {
        return $this->data['new_variations_attribute_set_id'] ?? 'new_attr_set_id';
    }

    /**
     * Custom method for ConfigurableProduct variation tests
     *
     * @param mixed $value
     * @return self
     */
    public function setNewVariationsAttributeSetId($value): self
    {
        $this->data['new_variations_attribute_set_id'] = $value;
        return $this;
    }

    /**
     * Custom method for ConfigurableProduct tests
     *
     * @param array $websiteIds
     * @return self
     */
    public function setWebsiteIds($websiteIds): self
    {
        $this->data['website_ids'] = $websiteIds;
        return $this;
    }

    /**
     * Override save method to avoid resource dependency
     *
     * @return self
     */
    public function save(): self
    {
        // Mock save method - no actual saving
        return $this;
    }

    /**
     * Override getWebsiteIds to work with our data array
     *
     * @return array|string
     */
    public function getWebsiteIds()
    {
        return $this->data['website_ids'] ?? [];
    }

    /**
     * Custom method for ConfigurableProduct tests
     *
     * @return array
     */
    public function getConfigurableAttributesData()
    {
        return $this->data['configurable_attributes_data'] ?? [];
    }

    /**
     * Custom method for ConfigurableProduct tests
     *
     * @param array $data
     * @return self
     */
    public function setConfigurableAttributesData(array $data): self
    {
        $this->data['configurable_attributes_data'] = $data;
        return $this;
    }

    /**
     * Custom method for ConfigurableProduct tests
     *
     * @param array $ids
     * @return self
     */
    public function setAssociatedProductIds(array $ids): self
    {
        $this->data['associated_product_ids'] = $ids;
        return $this;
    }

    /**
     * Override getTierPrice to prevent null errors
     *
     * @param mixed $qty
     * @param mixed $customerGroupId
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getTierPrice($qty = null, $customerGroupId = null)
    {
        return $this->data['tier_price'] ?? 0;
    }

    /**
     * Get media gallery images (custom method for testing)
     *
     * @return mixed
     */
    public function getMediaGalleryImages()
    {
        return $this->data['media_gallery_images'] ?? null;
    }

    /**
     * Set media gallery images (custom method for testing)
     *
     * @param mixed $images
     * @return self
     */
    public function setMediaGalleryImages($images): self
    {
        $this->data['media_gallery_images'] = $images;
        return $this;
    }

    /**
     * Get tax class ID for testing
     *
     * @return mixed
     */
    public function getTaxClassId()
    {
        if (isset($this->data['tax_class_id'])) {
            return $this->data['tax_class_id'];
        }
        $productData = $this->data['product_data'] ?? [];
        return $productData['tax_class_id'] ?? null;
    }

    /**
     * Set tax class ID for testing
     *
     * @param mixed $taxClassId
     * @return self
     */
    public function setTaxClassId($taxClassId): self
    {
        $this->data['tax_class_id'] = $taxClassId;
        return $this;
    }

    /**
     * Get grouped readonly for testing
     *
     * @return mixed
     */
    public function getGroupedReadonly()
    {
        return $this->data['grouped_readonly'] ?? null;
    }

    /**
     * Get grouped link data for testing
     *
     * @return mixed
     */
    public function getGroupedLinkData()
    {
        return $this->data['grouped_link_data'] ?? null;
    }

    /**
     * Set grouped link data for testing
     *
     * @param mixed $groupedLinkData
     * @return self
     */
    public function setGroupedLinkData($groupedLinkData): self
    {
        $this->data['grouped_link_data'] = $groupedLinkData;
        return $this;
    }

    // ========== DOWNLOADABLE-SPECIFIC METHODS ==========

    /**
     * Set downloadable data for testing
     *
     * @param mixed $data
     * @return self
     */
    public function setDownloadableData($data): self
    {
        $this->data['downloadable_data'] = $data;
        return $this;
    }

    /**
     * Get downloadable data for testing
     *
     * @return mixed
     */
    public function getDownloadableData()
    {
        return $this->data['downloadable_data'] ?? null;
    }

    /**
     * Get links title for downloadable products
     *
     * @return string|null
     */
    public function getLinksTitle()
    {
        return $this->data['links_title'] ?? null;
    }

    /**
     * Get samples title for downloadable products
     *
     * @return string|null
     */
    public function getSamplesTitle()
    {
        return $this->data['samples_title'] ?? null;
    }

    /**
     * Get links purchased separately flag
     *
     * @return bool|null
     */
    public function getLinksPurchasedSeparately()
    {
        return $this->data['links_purchased_separately'] ?? null;
    }

    /**
     * Set links purchased separately flag
     *
     * @param bool $flag
     * @return self
     */
    public function setLinksPurchasedSeparately(bool $flag): self
    {
        $this->data['links_purchased_separately'] = $flag;
        return $this;
    }

    /**
     * Set custom option changed flag
     *
     * @param bool $changed
     * @return self
     */
    public function setIsCustomOptionChanged($changed = true): self
    {
        $this->data['is_custom_option_changed'] = $changed;
        return $this;
    }

    /**
     * Set type has required options flag
     *
     * @param bool $hasRequired
     * @return self
     */
    public function setTypeHasRequiredOptions($hasRequired): self
    {
        $this->data['type_has_required_options'] = $hasRequired;
        return $this;
    }
    /**
     * Set required options
     *
     * @param mixed $required
     * @return self
     */
    public function setRequiredOptions($required): self
    {
        $this->data['required_options'] = $required;
        return $this;
    }

    /**
     * Set type has options flag
     *
     * @param bool $hasOptions
     * @return self
     */
    public function setTypeHasOptions($hasOptions): self
    {
        $this->data['type_has_options'] = $hasOptions;
        return $this;
    }

    /**
     * Set links exist flag
     *
     * @param bool $exist
     * @return self
     */
    public function setLinksExist($exist): self
    {
        $this->data['links_exist'] = $exist;
        return $this;
    }

    /**
     * Get downloadable links
     *
     * @return array
     */
    public function getDownloadableLinks()
    {
        return $this->data['downloadable_links'] ?? [];
    }

    /**
     * Get cart qty for tests.
     *
     * @return mixed
     */
    public function getCartQty()
    {
        return $this->getData('cart_qty');
    }

    /**
     * Return stick within parent flag from internal data.
     *
     * @return mixed
     */
    public function getStickWithinParent()
    {
        return $this->getData('stick_within_parent');
    }

    /**
     * Set stick within parent flag for tests.
     *
     * @param mixed $flag
     * @return $this
     */
    public function setStickWithinParent($flag)
    {
        $this->setData('stick_within_parent', $flag);
        return $this;
    }

    /**
     * Get parent product id for tests.
     *
     * @return mixed
     */
    public function getParentProductId()
    {
        return $this->getData('parent_product_id');
    }

    /**
     * Set parent product id for tests.
     *
     * @param mixed $id
     * @return $this
     */
    public function setParentProductId($id)
    {
        $this->setData('parent_product_id', $id);
        return $this;
    }

    /**
     * Get stock item for tests.
     *
     * @return mixed
     */
    public function getStockItem()
    {
        return $this->getData('stock_item');
    }

    /**
     * Enable/disable super mode flag for tests.
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsSuperMode($flag)
    {
        $this->setData('is_super_mode', (bool)$flag);
        return $this;
    }

    /**
     * Unset skip check required option flag for tests (no-op).
     *
     * @return $this
     */
    public function unsSkipCheckRequiredOption()
    {
        // No-op for unit tests, callable method required for mocks
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisibleInSiteVisibility()
    {
        return false;
    }

    /**
     * @param DataObject $data
     * @return $this
     */
    public function setUrlDataObject($data)
    {
        $this->urlDataObject = $data;
        return $this;
    }

    /**
     * Get URL data object.
     *
     * @return DataObject|null
     */
    public function getUrlDataObject()
    {
        return $this->urlDataObject;
    }

    /**
     * Check if URL data object exists.
     *
     * @return bool
     */
    public function hasUrlDataObject()
    {
        return (bool)$this->urlDataObject;
    }
}
