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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract model for product type implementation
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Catalog_Model_Product_Type_Abstract
{
    /**
     * Product type instance id
     *
     * @var string
     */
    protected $_typeId;

    /**
     *
     * @var array
     */
    protected $_editableAttributes;

    /**
     * Is a composite product type
     *
     * @var bool
     */
    protected $_isComposite = false;

    /**
     * Is a configurable product type
     *
     * @var bool
     */
    protected $_canConfigure = false;

    /**
     * Whether product quantity is fractional number or not
     *
     * @var bool
     */
    protected $_canUseQtyDecimals  = true;

    /**
     * File queue array
     *
     * @var array
     */
    protected $_fileQueue       = array();

    /**
     * Helpers list
     *
     * @var array
     */
    protected $_helpers = array();

    const CALCULATE_CHILD = 0;
    const CALCULATE_PARENT = 1;

    /**
     * values for shipment type (invoice etc)
     *
     */
    const SHIPMENT_SEPARATELY = 1;
    const SHIPMENT_TOGETHER = 0;

    /**
     * Process modes
     *
     * Full validation - all required options must be set, whole configuration
     * must be valid
     */
    const PROCESS_MODE_FULL = 'full';

    /**
     * Process modes
     *
     * Lite validation - only received options are validated
     */
    const PROCESS_MODE_LITE = 'lite';

    /**
     * Item options prefix
     */
    const OPTION_PREFIX = 'option_';

    /**
     * Delete data specific for this product type
     *
     * @param Mage_Catalog_Model_Product $product
     */
    abstract public function deleteTypeSpecificData(Mage_Catalog_Model_Product $product);

    /**
     * Initialize data
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_helpers = isset($data['helpers']) ? $data['helpers'] : array();
    }

    /**
     * Specify type identifier
     *
     * @param   string $typeId
     * @return  Mage_Catalog_Model_Product_Type_Abstract
     */
    public function setTypeId($typeId)
    {
        $this->_typeId = $typeId;
        return $this;
    }

    /**
     * Return relation info about used products for specific type instance
     *
     * @return Varien_Object Object with information data
     */
    public function getRelationInfo()
    {
        return new Varien_Object();
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return array();
    }

    /**
     * Retrieve parent ids array by requered child
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        return array();
    }

    /**
     * Get array of product set attributes
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getSetAttributes($product)
    {
        return $product->getResource()
            ->loadAllAttributes($product)
            ->getSortedAttributes($product->getAttributeSetId());
    }

    /**
     * Compare attribues sorting
     *
     * @param Mage_Catalog_Model_Entity_Attribute $attribute1
     * @param Mage_Catalog_Model_Entity_Attribute $attribute2
     * @return int
     */
    public function attributesCompare($attribute1, $attribute2)
    {
        $sort1 =  ($attribute1->getGroupSortPath() * 1000) + ($attribute1->getSortPath() * 0.0001);
        $sort2 =  ($attribute2->getGroupSortPath() * 1000) + ($attribute2->getSortPath() * 0.0001);

        if ($sort1 > $sort2) {
            return 1;
        } elseif ($sort1 < $sort2) {
            return -1;
        }

        return 0;
    }

    /**
     * Retrieve product type attributes
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getEditableAttributes($product)
    {
        $cacheKey = '_cache_editable_attributes';
        if (!$product->hasData($cacheKey)) {
            $editableAttributes = array();
            foreach ($this->getSetAttributes($product) as $attributeCode => $attribute) {
                $editableAttributes[$attributeCode] = $attribute;
            }
            $product->setData($cacheKey, $editableAttributes);
        }
        return $product->getData($cacheKey);
    }

    /**
     * Retrieve product attribute by identifier
     *
     * @param   int $attributeId
     * @return  Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttributeById($attributeId, $product)
    {
        foreach ($this->getSetAttributes($product) as $attribute) {
            if ($attribute->getId() == $attributeId) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * Check is virtual product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        return false;
    }

    /**
     * Check is product available for sale
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        $salable = $product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
        if ($salable && $product->hasData('is_salable')) {
            $salable = $product->getData('is_salable');
        }

        return (boolean) (int) $salable;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare options belonging to specific product type.
     *
     * @param  Varien_Object $buyRequest
     * @param  Mage_Catalog_Model_Product $product
     * @param  string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        // try to add custom options
        try {
            $options = $this->_prepareOptions($buyRequest, $product, $processMode);
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        }

        if (is_string($options)) {
            return $options;
        }
        // try to found super product configuration
        // (if product was buying within grouped product)
        $superProductConfig = $buyRequest->getSuperProductConfig();
        if (!empty($superProductConfig['product_id'])
            && !empty($superProductConfig['product_type'])
        ) {
            $superProductId = (int) $superProductConfig['product_id'];
            if ($superProductId) {
                if (!$superProduct = Mage::registry('used_super_product_'.$superProductId)) {
                    $superProduct = Mage::getModel('Mage_Catalog_Model_Product')->load($superProductId);
                    Mage::register('used_super_product_'.$superProductId, $superProduct);
                }
                if ($superProduct->getId()) {
                    $assocProductIds = $superProduct->getTypeInstance()->getAssociatedProductIds($superProduct);
                    if (in_array($product->getId(), $assocProductIds)) {
                        $productType = $superProductConfig['product_type'];
                        $product->addCustomOption('product_type', $productType, $superProduct);

                        $buyRequest->setData('super_product_config', array(
                            'product_type' => $productType,
                            'product_id'   => $superProduct->getId()
                        ));
                    }
                }
            }
        }

        $product->prepareCustomOptions();
        $buyRequest->unsetData('_processing_params'); // One-time params only
        $product->addCustomOption('info_buyRequest', serialize($buyRequest->getData()));

        if ($options) {
            $optionIds = array_keys($options);
            $product->addCustomOption('option_ids', implode(',', $optionIds));
            foreach ($options as $optionId => $optionValue) {
                $product->addCustomOption(self::OPTION_PREFIX . $optionId, $optionValue);
            }
        }

        // set quantity in cart
        if ($this->_isStrictProcessMode($processMode)) {
            $product->setCartQty($buyRequest->getQty());
        }
        $product->setQty($buyRequest->getQty());

        return array($product);
    }

    /**
     * Process product configuration
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    public function processConfiguration(Varien_Object $buyRequest, $product,
        $processMode = self::PROCESS_MODE_LITE)
    {
        $_products = $this->_prepareProduct($buyRequest, $product, $processMode);

        $this->processFileQueue();

        return $_products;
    }

    /**
     * Initialize product(s) for add to cart process.
     * Advanced version of func to prepare product for cart - processMode can be specified there.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param null|string $processMode
     * @return array|string
     */
    public function prepareForCartAdvanced(Varien_Object $buyRequest, $product, $processMode = null)
    {
        if (!$processMode) {
            $processMode = self::PROCESS_MODE_FULL;
        }
        $_products = $this->_prepareProduct($buyRequest, $product, $processMode);
        $this->processFileQueue();
        return $_products;
    }

    /**
     * Initialize product(s) for add to cart process
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @return array|string
     */
    public function prepareForCart(Varien_Object $buyRequest, $product)
    {
        return $this->prepareForCartAdvanced($buyRequest, $product, self::PROCESS_MODE_FULL);
    }

    /**
     * Process File Queue
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    public function processFileQueue()
    {
        if (empty($this->_fileQueue)) {
            return $this;
        }

        foreach ($this->_fileQueue as &$queueOptions) {
            if (isset($queueOptions['operation']) && $operation = $queueOptions['operation']) {
                switch ($operation) {
                    case 'receive_uploaded_file':
                        $src = isset($queueOptions['src_name']) ? $queueOptions['src_name'] : '';
                        $dst = isset($queueOptions['dst_name']) ? $queueOptions['dst_name'] : '';
                        /** @var $uploader Zend_File_Transfer_Adapter_Http */
                        $uploader = isset($queueOptions['uploader']) ? $queueOptions['uploader'] : null;

                        $path = dirname($dst);
                        $io = new Varien_Io_File();
                        if (!$io->isWriteable($path) && !$io->mkdir($path, 0777, true)) {
                            Mage::throwException($this->_helper('Mage_Catalog_Helper_Data')->__("Cannot create writeable directory '%s'.", $path));
                        }

                        $uploader->setDestination($path);

                        if (empty($src) || empty($dst) || !$uploader->receive($src)) {
                            /**
                             * @todo: show invalid option
                             */
                            if (isset($queueOptions['option'])) {
                                $queueOptions['option']->setIsValid(false);
                            }
                            Mage::throwException($this->_helper('Mage_Catalog_Helper_Data')->__("File upload failed"));
                        }
                        $this->_helper('Mage_Core_Helper_File_Storage_Database')->saveFile($dst);
                        break;
                    case 'move_uploaded_file':
                        $src = $queueOptions['src_name'];
                        $dst = $queueOptions['dst_name'];
                        move_uploaded_file($src, $dst);
                        $this->_helper('Mage_Core_Helper_File_Storage_Database')->saveFile($dst);
                        break;
                    default:
                        break;
                }
            }
            $queueOptions = null;
        }

        return $this;
    }

    /**
     * Add file to File Queue
     * @param array $queueOptions   Array of File Queue
     *                              (eg. ['operation'=>'move',
     *                                    'src_name'=>'filename',
     *                                    'dst_name'=>'filename2'])
     */
    public function addFileQueue($queueOptions)
    {
        $this->_fileQueue[] = $queueOptions;
    }

    /**
     * Check if current process mode is strict
     *
     * @param string $processMode
     * @return bool
     */
    protected function _isStrictProcessMode($processMode)
    {
        return $processMode == self::PROCESS_MODE_FULL;
    }

    /**
     * Retrieve message for specify option(s)
     *
     * @return string
     */
    public function getSpecifyOptionMessage()
    {
        return $this->_helper('Mage_Catalog_Helper_Data')->__('Please specify the product\'s required option(s).');
    }

    /**
     * Process custom defined options for product
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array
     */
    protected function _prepareOptions(Varien_Object $buyRequest, $product, $processMode)
    {
        $transport = new StdClass;
        $transport->options = array();
        foreach ($product->getOptions() as $_option) {
            /* @var $_option Mage_Catalog_Model_Product_Option */
            $group = $_option->groupFactory($_option->getType())
                ->setOption($_option)
                ->setProduct($product)
                ->setRequest($buyRequest)
                ->setProcessMode($processMode)
                ->validateUserValue($buyRequest->getOptions());

            $preparedValue = $group->prepareForCart();
            if ($preparedValue !== null) {
                $transport->options[$_option->getId()] = $preparedValue;
            }
        }

        $eventName = sprintf('catalog_product_type_prepare_%s_options', $processMode);
        Mage::dispatchEvent($eventName, array(
            'transport'   => $transport,
            'buy_request' => $buyRequest,
            'product' => $product
        ));
        return $transport->options;
    }

    /**
     * Check if product can be bought
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Abstract
     * @throws Mage_Core_Exception
     */
    public function checkProductBuyState($product)
    {
        if (!$product->getSkipCheckRequiredOption()) {
            foreach ($product->getOptions() as $option) {
                if ($option->getIsRequire()) {
                    $customOption = $product->getCustomOption(self::OPTION_PREFIX . $option->getId());
                    if (!$customOption || strlen($customOption->getValue()) == 0) {
                        $product->setSkipCheckRequiredOption(true);
                        Mage::throwException(
                            $this->_helper('Mage_Catalog_Helper_Data')->__('The product has required options')
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Prepare additional options/information for order item which will be
     * created from this product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getOrderOptions($product)
    {
        $optionArr = array();
        if ($info = $product->getCustomOption('info_buyRequest')) {
            $optionArr['info_buyRequest'] = unserialize($info->getValue());
        }

        if ($optionIds = $product->getCustomOption('option_ids')) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {

                    $confItemOption = $product->getCustomOption(self::OPTION_PREFIX . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setProduct($product)
                        ->setConfigurationItemOption($confItemOption);

                    $optionArr['options'][] = array(
                        'label' => $option->getTitle(),
                        'value' => $group->getFormattedOptionValue($confItemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($confItemOption->getValue()),
                        'option_id' => $option->getId(),
                        'option_type' => $option->getType(),
                        'option_value' => $confItemOption->getValue(),
                        'custom_view' => $group->isCustomizedView()
                    );
                }
            }
        }

        if ($productTypeConfig = $product->getCustomOption('product_type')) {
            $optionArr['super_product_config'] = array(
                'product_code'  => $productTypeConfig->getCode(),
                'product_type'  => $productTypeConfig->getValue(),
                'product_id'    => $productTypeConfig->getProductId()
            );
        }

        return $optionArr;
    }

    /**
     * Save type related data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    public function save($product)
    {
        if ($product->dataHasChangedFor('type_id') && $product->getOrigData('type_id')) {
            $oldTypeProduct = clone $product;
            $oldTypeInstance = Mage::getSingleton('Mage_Catalog_Model_Product_Type')
                ->factory($oldTypeProduct->setTypeId($product->getOrigData('type_id')));
            $oldTypeProduct->setTypeInstance($oldTypeInstance);
            $oldTypeInstance->deleteTypeSpecificData($oldTypeProduct);
        }
        return $this;
    }

    /**
     * Remove don't applicable attributes data
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _removeNotApplicableAttributes($product)
    {
        $eavConfig  = Mage::getSingleton('Mage_Eav_Model_Config');
        $entityType = $product->getResource()->getEntityType();
        foreach ($eavConfig->getEntityAttributeCodes($entityType, $product) as $attributeCode) {
            $attribute = $eavConfig->getAttribute($entityType, $attributeCode);
            $applyTo   = $attribute->getApplyTo();
            if (is_array($applyTo) && count($applyTo) > 0 && !in_array($product->getTypeId(), $applyTo)) {
                $product->unsetData($attribute->getAttributeCode());
            }
        }
    }

    /**
     * Before save type related data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    public function beforeSave($product)
    {
        $this->_removeNotApplicableAttributes($product);
        $product->canAffectOptions(true);
        return $this;
    }

    /**
     * Check if product is composite (grouped, configurable, etc)
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isComposite($product)
    {
        return $this->_isComposite;
    }

    /**
     * Check if product is configurable
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function canConfigure($product)
    {
        return $this->_canConfigure;
    }

    /**
     * Check if product qty is fractional number
     *
     * @return bool
     */
    public function canUseQtyDecimals()
    {
        return $this->_canUseQtyDecimals;
    }

    /**
     * Default action to get sku of product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getSku($product)
    {
        $sku = $product->getData('sku');
        if ($product->getCustomOption('option_ids')) {
            $sku = $this->getOptionSku($product,$sku);
        }
        return $sku;
    }

    /**
     * Default action to get sku of product with option
     *
     * @param Mage_Catalog_Model_Product $product Product with Custom Options
     * @param string $sku Product SKU without option
     * @return string
     */
    public function getOptionSku($product, $sku='')
    {
        $skuDelimiter = '-';
        if(empty($sku)){
            $sku = $product->getData('sku');
        }
        if ($optionIds = $product->getCustomOption('option_ids')) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {

                    $confItemOption = $product->getCustomOption(self::OPTION_PREFIX . $optionId);

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)->setListener(new Varien_Object());

                    if ($optionSku = $group->getOptionSku($confItemOption->getValue(), $skuDelimiter)) {
                        $sku .= $skuDelimiter . $optionSku;
                    }

                    if ($group->getListener()->getHasError()) {
                        $product->setHasError(true)
                                ->setMessage(
                                    $group->getListener()->getMessage()
                                );
                    }

                }
            }
        }
        return $sku;
    }
    /**
     * Default action to get weight of product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return decimal
     */
    public function getWeight($product)
    {
        return $product->getData('weight');
    }

    /**
     * Return true if product has options
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hasOptions($product)
    {
        if ($product->getHasOptions()) {
            return true;
        }
        if ($product->isRecurring()) {
            return true;
        }
        return false;
    }

    /**
     * Method is needed for specific actions to change given configuration options values
     * according current product type logic
     * Example: the cataloginventory validation of decimal qty can change qty to int,
     * so need to change configuration item qty option value too.
     *
     * @param array $options
     * @param Varien_Object $option
     * @param mixed $value
     * @param Mage_Catalog_Model_Product $product
     *
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    public function updateQtyOption($options, Varien_Object $option, $value, $product)
    {
        return $this;
    }

    /**
     * Check if product has required options
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hasRequiredOptions($product)
    {
        if ($product->getRequiredOptions()) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve store filter for associated products
     *
     * @return int|Mage_Core_Model_Store
     */
    public function getStoreFilter($product)
    {
        $cacheKey = '_cache_instance_store_filter';
        return $product->getData($cacheKey);
    }

    /**
     * Set store filter for associated products
     *
     * @param $store int|Mage_Core_Model_Store
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    public function setStoreFilter($store = null, $product)
    {
        $cacheKey = '_cache_instance_store_filter';
        $product->setData($cacheKey, $store);
        return $this;
    }

    /**
     * Allow for updates of children qty's
     * (applicable for complicated product types. As default returns false)
     *
     * @param Mage_Catalog_Model_Product $product
     * @return boolean false
     */
    public function getForceChildItemQtyChanges($product)
    {
        return false;
    }

    /**
     * Prepare Quote Item Quantity
     *
     * @param mixed $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function prepareQuoteItemQty($qty, $product)
    {
        return floatval($qty);
    }

    /**
     * Implementation of product specify logic of which product needs to be assigned to option.
     * For example if product which was added to option already removed from catalog.
     *
     * @param Mage_Catalog_Model_Product $optionProduct
     * @param Mage_Sales_Model_Quote_Item_Option $option
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    public function assignProductToOption($optionProduct, $option, $product)
    {
        if ($optionProduct) {
            $option->setProduct($optionProduct);
        } else {
            $option->setProduct($product);
        }

        return $this;
    }

    /**
     * Setting specified product type variables
     *
     * @param array $config
     * @return Mage_Catalog_Model_Product_Type_Abstract
     */
    public function setConfig($config)
    {
        if (isset($config['composite'])) {
            $this->_isComposite = (bool) $config['composite'];
        }

        if (isset($config['can_use_qty_decimals'])) {
            $this->_canUseQtyDecimals = (bool) $config['can_use_qty_decimals'];
        }

        return $this;
    }

    /**
     * Retrieve additional searchable data from type instance
     * Using based on product id and store_id data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getSearchableData($product)
    {
        $searchData = array();
        if ($product->getHasOptions()){
            $searchData = Mage::getSingleton('Mage_Catalog_Model_Product_Option')
                ->getSearchableData($product->getId(), $product->getStoreId());
        }

        return $searchData;
    }

    /**
     * Retrieve products divided into groups required to purchase
     * At least one product in each group has to be purchased
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductsToPurchaseByReqGroups($product)
    {
        if ($this->isComposite($product)) {
            return array();
        }
        return array(array($product));
    }

    /**
     * Prepare selected options for product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        return array();
    }

    /**
     * Check product's options configuration
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function checkProductConfiguration($product, $buyRequest)
    {
        $errors = array();

        try {
            /**
             * cloning product because prepareForCart() method will modify it
             */
            $productForCheck = clone $product;
            $buyRequestForCheck = clone $buyRequest;
            $result = $this->prepareForCart($buyRequestForCheck, $productForCheck);

            if (is_string($result)) {
               $errors[] = $result;
            }
        } catch (Mage_Core_Exception $e) {
            $errors[] = $e->getMessages();
        } catch (Exception $e) {
            Mage::logException($e);
            $errors[] = $this->_helper('Mage_Catalog_Helper_Data')->__('There was an error while request processing.');
        }

        return $errors;
    }

    /**
     * Check if Minimum advertise price is enabled at least in one option
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $visibility
     * @return bool
     */
    public function isMapEnabledInOptions($product, $visibility = null)
    {
        return false;
    }

    /**
     * Retrieve helper by specified name
     *
     * @param string $name
     * @return Mage_Core_Helper_Abstract
     */
    protected function _helper($name)
    {
        return isset($this->_helpers[$name]) ? $this->_helpers[$name] : Mage::helper($name);
    }

    /**
     * Determine presence of weight for product type
     *
     * @return bool
     */
    public function hasWeight()
    {
        return true;
    }
}
