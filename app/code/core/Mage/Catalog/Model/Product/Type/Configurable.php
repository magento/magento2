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
 * Configurable product type implementation
 *
 * This type builds in product attributes and existing simple products
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Abstract
{
    const TYPE_CODE = 'configurable';

    /**
     * Cache key for Used Product Attribute Ids
     *
     * @var string
     */
    protected $_usedProductAttributeIds = '_cache_instance_used_product_attribute_ids';

    /**
     * Cache key for Used Product Attributes
     *
     * @var string
     */
    protected $_usedProductAttributes   = '_cache_instance_used_product_attributes';

    /**
     * Cache key for Used Attributes
     *
     * @var string
     */
    protected $_usedAttributes          = '_cache_instance_used_attributes';

    /**
     * Cache key for configurable attributes
     *
     * @var string
     */
    protected $_configurableAttributes  = '_cache_instance_configurable_attributes';

    /**
     * Cache key for Used product ids
     *
     * @var string
     */
    protected $_usedProductIds          = '_cache_instance_product_ids';

    /**
     * Cache key for used products
     *
     * @var string
     */
    protected $_usedProducts            = '_cache_instance_products';

    /**
     * Product is composite
     *
     * @var bool
     */
    protected $_isComposite             = true;

    /**
     * Product is configurable
     *
     * @var bool
     */
    protected $_canConfigure            = true;

    /**
     * Return relation info about used products
     *
     * @return Varien_Object Object with information data
     */
    public function getRelationInfo()
    {
        $info = new Varien_Object();
        $info->setTable('catalog_product_super_link')
            ->setParentFieldName('parent_id')
            ->setChildFieldName('product_id');
        return $info;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param  int $parentId
     * @param  bool $required
     * @return array
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product_Type_Configurable')
            ->getChildrenIds($parentId, $required);
    }

    /**
     * Retrieve parent ids array by required child
     *
     * @param  int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        return Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product_Type_Configurable')
            ->getParentIdsByChild($childId);
    }

    /**
     * Check attribute availability for super product creation
     *
     * @param  Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return bool
     */
    public function canUseAttribute(Mage_Catalog_Model_Resource_Eav_Attribute $attribute)
    {
        return $attribute->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
            && $attribute->getIsVisible()
            && $attribute->getIsConfigurable()
            && $attribute->usesSource()
            && $attribute->getIsUserDefined();
    }

    /**
     * Declare attribute identifiers used for assign subproducts
     *
     * @param   array $ids
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Catalog_Model_Product_Type_Configurable
     */
    public function setUsedProductAttributeIds($ids, $product)
    {
        $usedProductAttributes  = array();
        $configurableAttributes = array();

        foreach ($ids as $attributeId) {
            $usedProductAttributes[]  = $this->getAttributeById($attributeId, $product);
            $configurableAttributes[] = Mage::getModel('Mage_Catalog_Model_Product_Type_Configurable_Attribute')
                ->setProductAttribute($this->getAttributeById($attributeId, $product));
        }
        $product->setData($this->_usedProductAttributes, $usedProductAttributes);
        $product->setData($this->_usedProductAttributeIds, $ids);
        $product->setData($this->_configurableAttributes, $configurableAttributes);

        return $this;
    }

    /**
     * Retrieve identifiers of used product attributes
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getUsedProductAttributeIds($product)
    {
        if (!$product->hasData($this->_usedProductAttributeIds)) {
            $usedProductAttributeIds = array();
            foreach ($this->getUsedProductAttributes($product) as $attribute) {
                $usedProductAttributeIds[] = $attribute->getId();
            }
            $product->setData($this->_usedProductAttributeIds, $usedProductAttributeIds);
        }
        return $product->getData($this->_usedProductAttributeIds);
    }

    /**
     * Retrieve used product attributes
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getUsedProductAttributes($product)
    {
        if (!$product->hasData($this->_usedProductAttributes)) {
            $usedProductAttributes = array();
            $usedAttributes        = array();
            foreach ($this->getConfigurableAttributes($product) as $attribute) {
                if (!is_null($attribute->getProductAttribute())) {
                    $id = $attribute->getProductAttribute()->getId();
                    $usedProductAttributes[$id] = $attribute->getProductAttribute();
                    $usedAttributes[$id]        = $attribute;
                }
            }
            $product->setData($this->_usedAttributes, $usedAttributes);
            $product->setData($this->_usedProductAttributes, $usedProductAttributes);
        }
        return $product->getData($this->_usedProductAttributes);
    }

    /**
     * Retrieve configurable attributes data
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getConfigurableAttributes($product)
    {
        Magento_Profiler::start('CONFIGURABLE:'.__METHOD__, array('group' => 'CONFIGURABLE', 'method' => __METHOD__));
        if (!$product->hasData($this->_configurableAttributes)) {
            $configurableAttributes = $this->getConfigurableAttributeCollection($product)
                ->orderByPosition()
                ->load();
            $product->setData($this->_configurableAttributes, $configurableAttributes);
        }
        Magento_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $product->getData($this->_configurableAttributes);
    }

    /**
     * Retrieve Configurable Attributes as array
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getConfigurableAttributesAsArray($product)
    {
        $res = array();
        foreach ($this->getConfigurableAttributes($product) as $attribute) {
            $eavAttribute = $attribute->getProductAttribute();
            /* @var $attribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            $res[$eavAttribute->getId()] = array(
                'id'             => $attribute->getId(),
                'label'          => $attribute->getLabel(),
                'use_default'    => $attribute->getUseDefault(),
                'position'       => $attribute->getPosition(),
                'values'         => $attribute->getPrices() ? $attribute->getPrices() : array(),
                'attribute_id'   => $eavAttribute->getId(),
                'attribute_code' => $eavAttribute->getAttributeCode(),
                'frontend_label' => $eavAttribute->getFrontend()->getLabel(),
                'store_label'    => $eavAttribute->getStoreLabel(),
                'options'        => $eavAttribute->getSource()->getAllOptions(false),
            );
        }
        return $res;
    }

    /**
     * Retrieve configurable attribute collection
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    public function getConfigurableAttributeCollection($product)
    {
        return Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection')
            ->setProductFilter($product);
    }


    /**
     * Retrieve subproducts identifiers
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getUsedProductIds($product)
    {
        if (!$product->hasData($this->_usedProductIds)) {
            $usedProductIds = array();
            foreach ($this->getUsedProducts($product) as $product) {
                $usedProductIds[] = $product->getId();
            }
            $product->setData($this->_usedProductIds, $usedProductIds);
        }
        return $product->getData($this->_usedProductIds);
    }

    /**
     * Retrieve array of "subproducts"
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  array $requiredAttributeIds
     * @return array
     */
    public function getUsedProducts($product, $requiredAttributeIds = null)
    {
        Magento_Profiler::start('CONFIGURABLE:'.__METHOD__, array('group' => 'CONFIGURABLE', 'method' => __METHOD__));
        if (!$product->hasData($this->_usedProducts)) {
            if (is_null($requiredAttributeIds)
                and is_null($product->getData($this->_configurableAttributes))) {
                // If used products load before attributes, we will load attributes.
                $this->getConfigurableAttributes($product);
                // After attributes loading products loaded too.
                Magento_Profiler::stop('CONFIGURABLE:'.__METHOD__);
                return $product->getData($this->_usedProducts);
            }

            $usedProducts = array();
            $collection = $this->getUsedProductCollection($product)
                ->addAttributeToSelect('*')
                ->addFilterByRequiredOptions();

            if (is_array($requiredAttributeIds)) {
                foreach ($requiredAttributeIds as $attributeId) {
                    $attribute = $this->getAttributeById($attributeId, $product);
                    if (!is_null($attribute))
                        $collection->addAttributeToFilter($attribute->getAttributeCode(), array('notnull'=>1));
                }
            }

            foreach ($collection as $item) {
                $usedProducts[] = $item;
            }

            $product->setData($this->_usedProducts, $usedProducts);
        }
        Magento_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $product->getData($this->_usedProducts);
    }

    /**
     * Retrieve related products collection
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Product_Collection
     */
    public function getUsedProductCollection($product)
    {
        $collection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Type_Configurable_Product_Collection')
            ->setFlag('require_stock_items', true)
            ->setFlag('product_children', true)
            ->setProductFilter($product);
        if (!is_null($this->getStoreFilter($product))) {
            $collection->addStoreFilter($this->getStoreFilter($product));
        }

        return $collection;
    }

    /**
     * Before save process
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    public function beforeSave($product)
    {
        parent::beforeSave($product);

        $product->canAffectOptions(false);

        if ($product->getCanSaveConfigurableAttributes()) {
            $product->canAffectOptions(true);
            $data = $product->getConfigurableAttributesData();
            if (!empty($data)) {
                foreach ($data as $attribute) {
                    if (!empty($attribute['values'])) {
                        $product->setTypeHasOptions(true);
                        $product->setTypeHasRequiredOptions(true);
                        break;
                    }
                }
            }
        }
        foreach ($this->getConfigurableAttributes($product) as $attribute) {
            $product->setData($attribute->getProductAttribute()->getAttributeCode(), null);
        }

        return $this;
    }

    /**
     * Save configurable product depended data
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    public function save($product)
    {
        parent::save($product);

        /* Save attributes information */
        $data = $product->getConfigurableAttributesData();
        if ($data) {
            foreach ($data as $attributeData) {
                /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
                $configurableAttribute = Mage::getModel('Mage_Catalog_Model_Product_Type_Configurable_Attribute');
                if (!empty($attributeData['id'])) {
                    $configurableAttribute->load($attributeData['id']);
                } else {
                    $configurableAttribute->loadByProductAndAttribute(
                        $product,
                        $this->getAttributeById($attributeData['attribute_id'], $product)
                    );
                }
                unset($attributeData['id']);
                $configurableAttribute
                   ->addData($attributeData)
                   ->setStoreId($product->getStoreId())
                   ->setProductId($product->getId())
                   ->save();
            }
            /** @var $configurableAttributesCollection Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection  */
            $configurableAttributesCollection = Mage::getResourceModel(
                'Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection'
            );
            $configurableAttributesCollection->setProductFilter($product);
            $configurableAttributesCollection->addFieldToFilter(
                'attribute_id',
                array('nin'=> $this->getUsedProductAttributeIds($product))
            );
            $configurableAttributesCollection->walk('delete');

        }

        /* Save product relations */
        $productIds = $product->getAssociatedProductIds();
        if (is_array($productIds)) {
            Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Type_Configurable')
                ->saveProducts($product, $productIds);
        }
        return $this;
    }

    /**
     * Check is product available for sale
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        $salable = parent::isSalable($product);

        if ($salable !== false) {
            $salable = false;
            if (!is_null($product)) {
                $this->setStoreFilter($product->getStoreId(), $product);
            }
            foreach ($this->getUsedProducts($product) as $child) {
                if ($child->isSalable()) {
                    $salable = true;
                    break;
                }
            }
        }

        return $salable;
    }

    /**
     * Check whether the product is available for sale
     * is alias to isSalable for compatibility
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function getIsSalable($product)
    {
        return $this->isSalable($product);
    }

    /**
     * Retrieve used product by attribute values
     *  $attributesInfo = array(
     *      $attributeId => $attributeValue
     *  )
     *
     * @param  array $attributesInfo
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product|null
     */
    public function getProductByAttributes($attributesInfo, $product)
    {
        if (is_array($attributesInfo) && !empty($attributesInfo)) {
            $productCollection = $this->getUsedProductCollection($product)->addAttributeToSelect('name');
            foreach ($attributesInfo as $attributeId => $attributeValue) {
                $productCollection->addAttributeToFilter($attributeId, $attributeValue);
            }
            $productObject = $productCollection->getFirstItem();
            if ($productObject->getId()) {
                return $productObject;
            }

            foreach ($this->getUsedProducts($product) as $productObject) {
                $checkRes = true;
                foreach ($attributesInfo as $attributeId => $attributeValue) {
                    $code = $this->getAttributeById($attributeId, $product)->getAttributeCode();
                    if ($productObject->getData($code) != $attributeValue) {
                        $checkRes = false;
                    }
                }
                if ($checkRes) {
                    return $productObject;
                }
            }
        }
        return null;
    }

    /**
     * Retrieve Selected Attributes info
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getSelectedAttributesInfo($product)
    {
        $attributes = array();
        Magento_Profiler::start('CONFIGURABLE:'.__METHOD__, array('group' => 'CONFIGURABLE', 'method' => __METHOD__));
        if ($attributesOption = $product->getCustomOption('attributes')) {
            $data = unserialize($attributesOption->getValue());
            $this->getUsedProductAttributeIds($product);

            $usedAttributes = $product->getData($this->_usedAttributes);

            foreach ($data as $attributeId => $attributeValue) {
                if (isset($usedAttributes[$attributeId])) {
                    $attribute = $usedAttributes[$attributeId];
                    $label = $attribute->getLabel();
                    $value = $attribute->getProductAttribute();
                    if ($value->getSourceModel()) {
                        $value = $value->getSource()->getOptionText($attributeValue);
                    }
                    else {
                        $value = '';
                    }

                    $attributes[] = array('label'=>$label, 'value'=>$value);
                }
            }
        }
        Magento_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $attributes;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then add Configurable specific options.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $attributes = $buyRequest->getSuperAttribute();
        if ($attributes || !$this->_isStrictProcessMode($processMode)) {
            if (!$this->_isStrictProcessMode($processMode)) {
                if (is_array($attributes)) {
                    foreach ($attributes as $key => $val) {
                        if (empty($val)) {
                            unset($attributes[$key]);
                        }
                    }
                } else {
                    $attributes = array();
                }
            }

            $result = parent::_prepareProduct($buyRequest, $product, $processMode);
            if (is_array($result)) {
                /**
                 * $attributes = array($attributeId=>$attributeValue)
                 */
                $subProduct = true;
                if ($this->_isStrictProcessMode($processMode)) {
                    foreach($this->getConfigurableAttributes($product) as $attributeItem){
                        /* @var $attributeItem Varien_Object */
                        $attrId = $attributeItem->getData('attribute_id');
                        if(!isset($attributes[$attrId]) || empty($attributes[$attrId])) {
                            $subProduct = null;
                            break;
                        }
                    }
                }
                if( $subProduct ) {
                    $subProduct = $this->getProductByAttributes($attributes, $product);
                }

                if ($subProduct) {
                    $product->addCustomOption('attributes', serialize($attributes));
                    $product->addCustomOption('product_qty_'.$subProduct->getId(), 1, $subProduct);
                    $product->addCustomOption('simple_product', $subProduct->getId(), $subProduct);

                    $_result = $subProduct->getTypeInstance()->_prepareProduct(
                        $buyRequest,
                        $subProduct,
                        $processMode
                    );
                    if (is_string($_result) && !is_array($_result)) {
                        return $_result;
                    }

                    if (!isset($_result[0])) {
                        return Mage::helper('Mage_Checkout_Helper_Data')->__('Cannot add the item to shopping cart');
                    }

                    /**
                     * Adding parent product custom options to child product
                     * to be sure that it will be unique as its parent
                     */
                    if ($optionIds = $product->getCustomOption('option_ids')) {
                        $optionIds = explode(',', $optionIds->getValue());
                        foreach ($optionIds as $optionId) {
                            if ($option = $product->getCustomOption('option_' . $optionId)) {
                                $_result[0]->addCustomOption('option_' . $optionId, $option->getValue());
                            }
                        }
                    }

                    $_result[0]->setParentProductId($product->getId())
                        // add custom option to simple product for protection of process
                        //when we add simple product separately
                        ->addCustomOption('parent_product_id', $product->getId());
                    if ($this->_isStrictProcessMode($processMode)) {
                        $_result[0]->setCartQty(1);
                    }
                    $result[] = $_result[0];
                    return $result;
                } else if (!$this->_isStrictProcessMode($processMode)) {
                    return $result;
                }
            }
        }

        return $this->getSpecifyOptionMessage();
    }

    /**
     * Check if product can be bought
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Configurable
     * @throws Mage_Core_Exception
     */
    public function checkProductBuyState($product)
    {
        parent::checkProductBuyState($product);
        $option = $product->getCustomOption('info_buyRequest');
        if ($option instanceof Mage_Sales_Model_Quote_Item_Option) {
            $buyRequest = new Varien_Object(unserialize($option->getValue()));
            $attributes = $buyRequest->getSuperAttribute();
            if (is_array($attributes)) {
                foreach ($attributes as $key => $val) {
                    if (empty($val)) {
                        unset($attributes[$key]);
                    }
                }
            }
            if (empty($attributes)) {
                Mage::throwException($this->getSpecifyOptionMessage());
            }
        }
        return $this;
    }

    /**
     * Retrieve message for specify option(s)
     *
     * @return string
     */
    public function getSpecifyOptionMessage()
    {
        return Mage::helper('Mage_Catalog_Helper_Data')->__('Please specify the product\'s option(s).');
    }

    /**
     * Prepare additional options/information for order item which will be
     * created from this product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getOrderOptions($product)
    {
        $options = parent::getOrderOptions($product);
        $options['attributes_info'] = $this->getSelectedAttributesInfo($product);
        if ($simpleOption = $product->getCustomOption('simple_product')) {
            $options['simple_name'] = $simpleOption->getProduct()->getName();
            $options['simple_sku']  = $simpleOption->getProduct()->getSku();
        }

        $options['product_calculations'] = self::CALCULATE_PARENT;
        $options['shipment_type'] = self::SHIPMENT_TOGETHER;

        return $options;
    }

    /**
     * Check is virtual product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        if ($productOption = $product->getCustomOption('simple_product')) {
            if ($optionProduct = $productOption->getProduct()) {
                /* @var $optionProduct Mage_Catalog_Model_Product */
                return $optionProduct->isVirtual();
            }
        }
        return parent::isVirtual($product);
    }

    /**
     * Return true if product has options
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hasOptions($product)
    {
        if ($product->getOptions()) {
            return true;
        }

        $attributes = $this->getConfigurableAttributes($product);
        if (count($attributes)) {
            foreach ($attributes as $attribute) {
                /** @var Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute */
                if ($attribute->getData('prices')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return product weight based on simple product
     * weight or configurable product weight
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return decimal
     */
    public function getWeight($product)
    {
        if ($product->hasCustomOptions() &&
            ($simpleProductOption = $product->getCustomOption('simple_product'))
        ) {
            $simpleProduct = $simpleProductOption->getProduct();
            if ($simpleProduct) {
                return $simpleProduct->getWeight();
            }
        }

        return $product->getData('weight');
    }

    /**
     * Implementation of product specify logic of which product needs to be assigned to option.
     * For example if product which was added to option already removed from catalog.
     *
     * @param  Mage_Catalog_Model_Product|null $optionProduct
     * @param  Mage_Sales_Model_Quote_Item_Option $option
     * @param  Mage_Catalog_Model_Product|null $product
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    public function assignProductToOption($optionProduct, $option, $product)
    {
        if ($optionProduct) {
            $option->setProduct($optionProduct);
        } else {
            $option->getItem()->setHasConfigurationUnavailableError(true);
        }
        return $this;
    }

    /**
     * Retrieve products divided into groups required to purchase
     * At least one product in each group has to be purchased
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductsToPurchaseByReqGroups($product)
    {
        return array($this->getUsedProducts($product));
    }

    /**
     * Get sku of product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getSku($product)
    {
        $simpleOption = $product->getCustomOption('simple_product');
        if($simpleOption) {
            $optionProduct = $simpleOption->getProduct();
            $simpleSku = null;
            if ($optionProduct) {
                $simpleSku =  $simpleOption->getProduct()->getSku();
            }
            $sku = parent::getOptionSku($product, $simpleSku);
        } else {
            $sku = parent::getSku($product);
        }

        return $sku;
    }

    /**
     * Prepare selected options for configurable product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $superAttribute = $buyRequest->getSuperAttribute();
        $superAttribute = (is_array($superAttribute)) ? array_filter($superAttribute, 'intval') : array();

        $options = array('super_attribute' => $superAttribute);

        return $options;
    }

    /**
     * Check if Minimum Advertise Price is enabled at least in one option
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $visibility
     * @return bool|null
     */
    public function isMapEnabledInOptions($product, $visibility = null)
    {
        return null;
    }

    /**
     * Prepare and retrieve options values with product data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getConfigurableOptions($product)
    {
        return Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product_Type_Configurable')
            ->getConfigurableOptions($product, $this->getUsedProductAttributes($product));
    }

    /**
     * Check that product of this type has weight
     *
     * @return bool
     */
    public function hasWeight()
    {
        return false;
    }

    /**
     * Delete data specific for Configurable product type
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function deleteTypeSpecificData(Mage_Catalog_Model_Product $product)
    {
        Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Type_Configurable')
            ->saveProducts($product, array());
        /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
        $configurableAttribute = Mage::getModel('Mage_Catalog_Model_Product_Type_Configurable_Attribute');
        $configurableAttribute->deleteByProduct($product);
    }

    /**
     * Retrieve product attribute by identifier
     * Difference from abstract: any attribute is available, not just the ones from $product's attribute set
     *
     * @param  int $attributeId
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttributeById($attributeId, $product)
    {
        $attribute = parent::getAttributeById($attributeId, $product);
        return $attribute ?: Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute')->load($attributeId);
    }

    /**
     * Generate simple products to link with configurable
     *
     * @param Mage_Catalog_Model_Product $parentProduct
     * @param array $productsData
     * @return array
     */
    public function generateSimpleProducts($parentProduct, $productsData)
    {
        $this->_prepareAttributeSetToBeBaseForNewVariations($parentProduct);
        $generatedProductIds = array();
        foreach ($productsData as $simpleProductData) {
            $newSimpleProduct = Mage::getModel('Mage_Catalog_Model_Product');
            $configurableAttribute = Mage::helper('Mage_Core_Helper_Data')->jsonDecode(
                $simpleProductData['configurable_attribute']
            );
            unset($simpleProductData['configurable_attribute']);

            $this->_fillSimpleProductData(
                $newSimpleProduct,
                $parentProduct,
                array_merge($simpleProductData, $configurableAttribute)
            );
            $newSimpleProduct->save();

            $generatedProductIds[] = $newSimpleProduct->getId();
        }
        return $generatedProductIds;
    }

    /**
     * Set image for product without image if possible
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    public function setImageFromChildProduct(Mage_Catalog_Model_Product $product)
    {
        if (!$product->getData('image') || $product->getData('image') === 'no_selection') {
            foreach ($this->getUsedProducts($product) as $childProduct) {
                if ($childProduct->getData('image') && $childProduct->getData('image') !== 'no_selection') {
                    $product->setImage($childProduct->getData('image'));
                    break;
                }
            }
        }
        return parent::setImageFromChildProduct($product);
    }

    /**
     * Prepare attribute set comprising all selected configurable attributes
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _prepareAttributeSetToBeBaseForNewVariations(Mage_Catalog_Model_Product $product)
    {
        $attributes = $this->getUsedProductAttributes($product);
        $attributeSetId = $product->getNewVariationsAttributeSetId();
        /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
        $attributeSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set')->load($attributeSetId);
        $attributeSet->addSetInfo(
            Mage::getModel('Mage_Eav_Model_Entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId(),
            $attributes
        );
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Entity_Attribute */
            if (!$attribute->isInSet($attributeSetId)) {
                $attribute->setAttributeSetId($attributeSetId)
                    ->setAttributeGroupId($attributeSet->getDefaultGroupId($attributeSetId))
                    ->save();
            }
        }
    }

    /**
     * Fill simple product data during generation
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Product $parentProduct
     * @param array $postData
     */
    protected function _fillSimpleProductData(
        Mage_Catalog_Model_Product $product,
        Mage_Catalog_Model_Product $parentProduct,
        $postData
    ) {
        $product->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->setTypeId($postData['weight']
                ? Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                : Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL
            )->setAttributeSetId($parentProduct->getNewVariationsAttributeSetId());

        foreach ($product->getTypeInstance()->getEditableAttributes($product) as $attribute) {
            if ($attribute->getIsUnique()
                || $attribute->getAttributeCode() == 'url_key'
                || $attribute->getFrontend()->getInputType() == 'gallery'
                || $attribute->getFrontend()->getInputType() == 'media_image'
                || !$attribute->getIsVisible()
            ) {
                continue;
            }

            $product->setData(
                $attribute->getAttributeCode(),
                $parentProduct->getData($attribute->getAttributeCode())
            );
        }

        if (!isset($postData['stock_data']['use_config_manage_stock'])) {
            $postData['stock_data']['use_config_manage_stock'] = 0;
        }
        $product->addData($postData)
            ->setWebsiteIds($parentProduct->getWebsiteIds())
            ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
    }
}
