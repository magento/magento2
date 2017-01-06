<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * Abstract model for product type implementation
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractType
{
    /**
     * Product type instance id
     *
     * @var string
     */
    protected $_typeId;

    /**
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
     * If product can be configured
     *
     * @var bool
     */
    protected $_canConfigure = false;

    /**
     * Whether product quantity is fractional number or not
     *
     * @var bool
     */
    protected $_canUseQtyDecimals = true;

    /**
     * File queue array
     *
     * @var array
     */
    protected $_fileQueue = [];

    const CALCULATE_CHILD = 0;

    const CALCULATE_PARENT = 1;

    /**#@+
     * values for shipment type (invoice etc)
     */
    const SHIPMENT_SEPARATELY = 1;

    const SHIPMENT_TOGETHER = 0;
    /**#@-*/

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
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_fileStorageDb;

    /**
     * Cache key for Product Attributes
     *
     * @var string
     */
    protected $_cacheProductSetAttributes = '_cache_instance_product_set_attributes';

    /**
     * Delete data specific for this product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    abstract public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product);

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Catalog product type
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_catalogProductType;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Catalog product option
     *
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $_catalogProductOption;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_catalogProductOption = $catalogProductOption;
        $this->_eavConfig = $eavConfig;
        $this->_catalogProductType = $catalogProductType;
        $this->_coreRegistry = $coreRegistry;
        $this->_eventManager = $eventManager;
        $this->_fileStorageDb = $fileStorageDb;
        $this->_filesystem = $filesystem;
        $this->_logger = $logger;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Specify type identifier
     *
     * @param   string $typeId
     * @return  \Magento\Catalog\Model\Product\Type\AbstractType
     */
    public function setTypeId($typeId)
    {
        $this->_typeId = $typeId;
        return $this;
    }

    /**
     * Return relation info about used products for specific type instance
     *
     * @return \Magento\Framework\DataObject Object with information data
     */
    public function getRelationInfo()
    {
        return new \Magento\Framework\DataObject();
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @deplacated TODO: refactor to child relation manager
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return [];
    }

    /**
     * Retrieve parent ids array by requered child
     *
     * @param int|array $childId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getParentIdsByChild($childId)
    {
        return [];
    }

    /**
     * Get array of product set attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute[]
     */
    public function getSetAttributes($product)
    {
        if (!$product->hasData($this->_cacheProductSetAttributes)) {
            $setAttributes = $product->getResource()
                ->loadAllAttributes($product)
                ->getSortedAttributes($product->getAttributeSetId());
            $product->setData($this->_cacheProductSetAttributes, $setAttributes);
        }
        return $product->getData($this->_cacheProductSetAttributes);
    }

    /**
     * Compare attributes sorting
     *
     * @param \Magento\Catalog\Model\Entity\Attribute $attributeOne
     * @param \Magento\Catalog\Model\Entity\Attribute $attributeTwo
     * @return int
     */
    public function attributesCompare($attributeOne, $attributeTwo)
    {
        $sortOne = $attributeOne->getGroupSortPath() * 1000 + $attributeOne->getSortPath() * 0.0001;
        $sortTwo = $attributeTwo->getGroupSortPath() * 1000 + $attributeTwo->getSortPath() * 0.0001;

        if ($sortOne > $sortTwo) {
            return 1;
        } elseif ($sortOne < $sortTwo) {
            return -1;
        }

        return 0;
    }

    /**
     * Retrieve product type attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute[]
     */
    public function getEditableAttributes($product)
    {
        return $this->getSetAttributes($product);
    }

    /**
     * Retrieve product attribute by identifier
     *
     * @param  int $attributeId
     * @param  \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute|null
     */
    public function getAttributeById($attributeId, $product)
    {
        if ($attributeId) {
            foreach ($this->getSetAttributes($product) as $attribute) {
                if ($attribute->getId() == $attributeId) {
                    return $attribute;
                }
            }
        }
        return null;
    }

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isVirtual($product)
    {
        return false;
    }

    /**
     * Check is product available for sale
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        $salable = $product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        if ($salable && $product->hasData('is_salable')) {
            $salable = $product->getData('is_salable');
        }

        return (bool)(int)$salable;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare options belonging to specific product type.
     *
     * @param  \Magento\Framework\DataObject $buyRequest
     * @param  \Magento\Catalog\Model\Product $product
     * @param  string $processMode
     * @return array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        // try to add custom options
        try {
            $options = $this->_prepareOptions($buyRequest, $product, $processMode);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $e->getMessage();
        }

        if (is_string($options)) {
            return $options;
        }
        // try to found super product configuration
        $superProductConfig = $buyRequest->getSuperProductConfig();
        if (!empty($superProductConfig['product_id']) && !empty($superProductConfig['product_type'])) {
            $superProductId = (int)$superProductConfig['product_id'];
            if ($superProductId) {
                /** @var \Magento\Catalog\Model\Product $superProduct */
                $superProduct = $this->_coreRegistry->registry('used_super_product_' . $superProductId);
                if (!$superProduct) {
                    $superProduct = $this->productRepository->getById($superProductId);
                    $this->_coreRegistry->register('used_super_product_' . $superProductId, $superProduct);
                }
                $assocProductIds = $superProduct->getTypeInstance()->getAssociatedProductIds($superProduct);
                if (in_array($product->getId(), $assocProductIds)) {
                    $productType = $superProductConfig['product_type'];
                    $product->addCustomOption('product_type', $productType, $superProduct);

                    $buyRequest->setData(
                        'super_product_config',
                        ['product_type' => $productType, 'product_id' => $superProduct->getId()]
                    );
                }
            }
        }

        $product->prepareCustomOptions();
        $buyRequest->unsetData('_processing_params');
        // One-time params only
        $product->addCustomOption('info_buyRequest', $this->serializer->serialize($buyRequest->getData()));
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

        return [$product];
    }

    /**
     * Process product configuration
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array|string
     */
    public function processConfiguration(
        \Magento\Framework\DataObject $buyRequest,
        $product,
        $processMode = self::PROCESS_MODE_LITE
    ) {
        $products = $this->_prepareProduct($buyRequest, $product, $processMode);
        $this->processFileQueue();
        return $products;
    }

    /**
     * Initialize product(s) for add to cart process.
     * Advanced version of func to prepare product for cart - processMode can be specified there.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param null|string $processMode
     * @return array|string
     */
    public function prepareForCartAdvanced(\Magento\Framework\DataObject $buyRequest, $product, $processMode = null)
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
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @return array|string
     */
    public function prepareForCart(\Magento\Framework\DataObject $buyRequest, $product)
    {
        return $this->prepareForCartAdvanced($buyRequest, $product, self::PROCESS_MODE_FULL);
    }

    /**
     * Process File Queue
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processFileQueue()
    {
        if (empty($this->_fileQueue)) {
            return $this;
        }

        foreach ($this->_fileQueue as &$queueOptions) {
            if (isset($queueOptions['operation']) && ($operation = $queueOptions['operation'])) {
                switch ($operation) {
                    case 'receive_uploaded_file':
                        $src = isset($queueOptions['src_name']) ? $queueOptions['src_name'] : '';
                        $dst = isset($queueOptions['dst_name']) ? $queueOptions['dst_name'] : '';
                        /** @var $uploader \Zend_File_Transfer_Adapter_Http */
                        $uploader = isset($queueOptions['uploader']) ? $queueOptions['uploader'] : null;

                        $path = dirname($dst);

                        try {
                            $rootDir = $this->_filesystem->getDirectoryWrite(
                                DirectoryList::ROOT
                            );
                            $rootDir->create($rootDir->getRelativePath($path));
                        } catch (\Magento\Framework\Exception\FileSystemException $e) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('We can\'t create writeable directory "%1".', $path)
                            );
                        }

                        $uploader->setDestination($path);

                        if (empty($src) || empty($dst) || !$uploader->receive($src)) {
                            /**
                             * @todo: show invalid option
                             */
                            if (isset($queueOptions['option'])) {
                                $queueOptions['option']->setIsValid(false);
                            }
                            throw new \Magento\Framework\Exception\LocalizedException(__('The file upload failed.'));
                        }
                        $this->_fileStorageDb->saveFile($dst);
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
     * @return void
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
     * @return \Magento\Framework\Phrase
     */
    public function getSpecifyOptionMessage()
    {
        return __('Please specify product\'s required option(s).');
    }

    /**
     * Process custom defined options for product
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array
     * @throws LocalizedException
     */
    protected function _prepareOptions(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $transport = new \StdClass();
        $transport->options = [];
        $options = null;
        if ($product->getHasOptions()) {
            $options = $product->getOptions();
        }
        if ($options !== null) {
            $results = [];
            foreach ($options as $option) {
                /* @var $option \Magento\Catalog\Model\Product\Option */
                try {
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setProduct($product)
                        ->setRequest($buyRequest)
                        ->setProcessMode($processMode)
                        ->validateUserValue($buyRequest->getOptions());
                } catch (LocalizedException $e) {
                    $results[] = $e->getMessage();
                    continue;
                }

                $preparedValue = $group->prepareForCart();
                if ($preparedValue !== null) {
                    $transport->options[$option->getId()] = $preparedValue;
                }
            }
            if (count($results) > 0) {
                throw new LocalizedException(__(implode("\n", $results)));
            }
        }

        $eventName = sprintf('catalog_product_type_prepare_%s_options', $processMode);
        $this->_eventManager->dispatch(
            $eventName,
            ['transport' => $transport, 'buy_request' => $buyRequest, 'product' => $product]
        );
        return $transport->options;
    }

    /**
     * Check if product can be bought
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkProductBuyState($product)
    {
        if (!$product->getSkipCheckRequiredOption() && $product->getHasOptions()) {
            $options = $product->getProductOptionsCollection();
            foreach ($options as $option) {
                if ($option->getIsRequire()) {
                    $customOption = $product->getCustomOption(self::OPTION_PREFIX . $option->getId());
                    if (!$customOption || strlen($customOption->getValue()) == 0) {
                        $product->setSkipCheckRequiredOption(true);
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The product has required options.')
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
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getOrderOptions($product)
    {
        $optionArr = [];
        $info = $product->getCustomOption('info_buyRequest');
        if ($info) {
            $optionArr['info_buyRequest'] = $this->serializer->unserialize($info->getValue());
        }

        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
                if ($option) {
                    $confItemOption = $product->getCustomOption(self::OPTION_PREFIX . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setProduct($product)
                        ->setConfigurationItemOption($confItemOption);

                    $optionArr['options'][] = [
                        'label' => $option->getTitle(),
                        'value' => $group->getFormattedOptionValue($confItemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($confItemOption->getValue()),
                        'option_id' => $option->getId(),
                        'option_type' => $option->getType(),
                        'option_value' => $confItemOption->getValue(),
                        'custom_view' => $group->isCustomizedView(),
                    ];
                }
            }
        }

        $productTypeConfig = $product->getCustomOption('product_type');
        if ($productTypeConfig) {
            $optionArr['super_product_config'] = [
                'product_code' => $productTypeConfig->getCode(),
                'product_type' => $productTypeConfig->getValue(),
                'product_id' => $productTypeConfig->getProductId(),
            ];
        }

        return $optionArr;
    }

    /**
     * Save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function save($product)
    {
        if ($product->dataHasChangedFor('type_id') && $product->getOrigData('type_id')) {
            $oldTypeProduct = clone $product;
            $oldTypeInstance = $this->_catalogProductType->factory(
                $oldTypeProduct->setTypeId($product->getOrigData('type_id'))
            );
            $oldTypeProduct->setTypeInstance($oldTypeInstance);
            $oldTypeInstance->deleteTypeSpecificData($oldTypeProduct);
        }
        return $this;
    }

    /**
     * Remove don't applicable attributes data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function _removeNotApplicableAttributes($product)
    {
        $entityType = $product->getResource()->getEntityType();
        foreach ($this->_eavConfig->getEntityAttributeCodes($entityType, $product) as $attributeCode) {
            $attribute = $this->_eavConfig->getAttribute($entityType, $attributeCode);
            $applyTo = $attribute->getApplyTo();
            if (is_array($applyTo) && count($applyTo) > 0 && !in_array($product->getTypeId(), $applyTo)) {
                $product->unsetData($attribute->getAttributeCode());
            }
        }
    }

    /**
     * Before save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function beforeSave($product)
    {
        $this->_removeNotApplicableAttributes($product);
        $product->canAffectOptions(true);
        return $this;
    }

    /**
     * Check if product is composite
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isComposite($product)
    {
        return $this->_isComposite;
    }

    /**
     * Check if product can be configured
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getSku($product)
    {
        $sku = $product->getData('sku');
        if ($product->getCustomOption('option_ids')) {
            $sku = $this->getOptionSku($product, $sku);
        }
        return $sku;
    }

    /**
     * Default action to get sku of product with option
     *
     * @param \Magento\Catalog\Model\Product $product Product with Custom Options
     * @param string $sku Product SKU without option
     * @return string
     */
    public function getOptionSku($product, $sku = '')
    {
        $skuDelimiter = '-';
        if (empty($sku)) {
            $sku = $product->getData('sku');
        }
        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
                if ($option) {
                    $confItemOption = $product->getCustomOption(self::OPTION_PREFIX . $optionId);

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setListener(new \Magento\Framework\DataObject());

                    $optionSku = $group->getOptionSku($confItemOption->getValue(), $skuDelimiter);
                    if ($optionSku) {
                        $sku .= $skuDelimiter . $optionSku;
                    }

                    if ($group->getListener()->getHasError()) {
                        $product->setHasError(true)->setMessage($group->getListener()->getMessage());
                    }
                }
            }
        }
        return $sku;
    }

    /**
     * Default action to get weight of product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getWeight($product)
    {
        return $product->getData('weight');
    }

    /**
     * Return true if product has options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasOptions($product)
    {
        return $product->getHasOptions();
    }

    /**
     * Method is needed for specific actions to change given configuration options values
     * according current product type logic
     * Example: the cataloginventory validation of decimal qty can change qty to int,
     * so need to change configuration item qty option value too.
     *
     * @param array $options
     * @param \Magento\Framework\DataObject $option
     * @param int|float|null $value
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateQtyOption($options, \Magento\Framework\DataObject $option, $value, $product)
    {
        return $this;
    }

    /**
     * Check if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
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
     * @param \Magento\Catalog\Model\Product $product
     * @return int|\Magento\Store\Model\Store
     */
    public function getStoreFilter($product)
    {
        $cacheKey = '_cache_instance_store_filter';
        return $product->getData($cacheKey);
    }

    /**
     * Set store filter for associated products
     *
     * @param $store int|\Magento\Store\Model\Store
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setStoreFilter($store, $product)
    {
        $cacheKey = '_cache_instance_store_filter';
        $product->setData($cacheKey, $store);
        return $this;
    }

    /**
     * Allow for updates of children qty's
     * (applicable for complicated product types. As default returns false)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean false
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getForceChildItemQtyChanges($product)
    {
        return false;
    }

    /**
     * Prepare Quote Item Quantity
     *
     * @param int|float $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareQuoteItemQty($qty, $product)
    {
        return floatval($qty);
    }

    /**
     * Implementation of product specify logic of which product needs to be assigned to option.
     * For example if product which was added to option already removed from catalog.
     *
     * @param \Magento\Catalog\Model\Product $optionProduct
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
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
     * @return $this
     */
    public function setConfig($config)
    {
        if (isset($config['composite'])) {
            $this->_isComposite = (bool)$config['composite'];
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
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getSearchableData($product)
    {
        $searchData = [];
        if ($product->getHasOptions()) {
            $searchData = $this->_catalogProductOption->getSearchableData(
                $product->getEntityId(),
                $product->getStoreId()
            );
        }

        return $searchData;
    }

    /**
     * Retrieve products divided into groups required to purchase
     * At least one product in each group has to be purchased
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductsToPurchaseByReqGroups($product)
    {
        if ($this->isComposite($product)) {
            return [];
        }
        return [[$product]];
    }

    /**
     * Prepare selected options for product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Framework\DataObject $buyRequest
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processBuyRequest($product, $buyRequest)
    {
        return [];
    }

    /**
     * Check product's options configuration
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Framework\DataObject $buyRequest
     * @return array
     */
    public function checkProductConfiguration($product, $buyRequest)
    {
        $errors = [];

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
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $errors[] = $e->getMessage();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $errors[] = __('Something went wrong while processing the request.');
        }

        return $errors;
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

    /**
     * Set image for product without image if possible
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setImageFromChildProduct(\Magento\Catalog\Model\Product $product)
    {
        return $this;
    }

    /**
     * Return array of specific to type product entities
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdentities(\Magento\Catalog\Model\Product $product)
    {
        return [];
    }

    /**
     * @param \Magento\Catalog\Model\Product\Type\AbstractType $product
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAssociatedProducts($product)
    {
        return [];
    }

    /**
     * Check if product can be potentially buyed from the category page or some other list
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isPossibleBuyFromList($product)
    {
        return !$this->hasRequiredOptions($product);
    }
}
