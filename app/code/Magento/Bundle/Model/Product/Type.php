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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Model\Product;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Bundle Type Model
 */
class Type extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    /**
     * Product is composite
     *
     * @var bool
     */
    protected $_isComposite = true;

    /**
     * Cache key for Options Collection
     *
     * @var string
     */
    protected $_keyOptionsCollection = '_cache_instance_options_collection';

    /**
     * Cache key for Selections Collection
     *
     * @var string
     */
    protected $_keySelectionsCollection = '_cache_instance_selections_collection';

    /**
     * Cache key for used Selections
     *
     * @var string
     */
    protected $_keyUsedSelections = '_cache_instance_used_selections';

    /**
     * Cache key for used selections ids
     *
     * @var string
     */
    protected $_keyUsedSelectionsIds = '_cache_instance_used_selections_ids';

    /**
     * Cache key for used options
     *
     * @var string
     */
    protected $_keyUsedOptions = '_cache_instance_used_options';

    /**
     * Cache key for used options ids
     *
     * @var string
     */
    protected $_keyUsedOptionsIds = '_cache_instance_used_options_ids';

    /**
     * Product is possible to configure
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct = null;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Bundle\Model\OptionFactory
     */
    protected $_bundleOption;

    /**
     * @var \Magento\Bundle\Model\Resource\Selection
     */
    protected $_bundleSelection;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Bundle\Model\Resource\Selection\CollectionFactory
     */
    protected $_bundleCollection;

    /**
     * @var \Magento\Bundle\Model\Resource\BundleFactory
     */
    protected $_bundleFactory;

    /**
     * @var \Magento\Bundle\Model\SelectionFactory $bundleModelSelection
     */
    protected $_bundleModelSelection;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Bundle\Model\SelectionFactory $bundleModelSelection
     * @param \Magento\Bundle\Model\Resource\BundleFactory $bundleFactory
     * @param \Magento\Bundle\Model\Resource\Selection\CollectionFactory $bundleCollection
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Bundle\Model\Resource\Selection $bundleSelection
     * @param \Magento\Bundle\Model\OptionFactory $bundleOption
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Logger $logger,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Bundle\Model\SelectionFactory $bundleModelSelection,
        \Magento\Bundle\Model\Resource\BundleFactory $bundleFactory,
        \Magento\Bundle\Model\Resource\Selection\CollectionFactory $bundleCollection,
        \Magento\Catalog\Model\Config $config,
        \Magento\Bundle\Model\Resource\Selection $bundleSelection,
        \Magento\Bundle\Model\OptionFactory $bundleOption,
        \Magento\Framework\StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        array $data = array()
    ) {
        $this->_catalogProduct = $catalogProduct;
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->_bundleOption = $bundleOption;
        $this->_bundleSelection = $bundleSelection;
        $this->_config = $config;
        $this->_bundleCollection = $bundleCollection;
        $this->_bundleFactory = $bundleFactory;
        $this->_bundleModelSelection = $bundleModelSelection;
        $this->priceCurrency = $priceCurrency;
        parent::__construct(
            $productFactory,
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $coreData,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $data
        );
    }

    /**
     * Return relation info about used products
     *
     * @return \Magento\Framework\Object Object with information data
     */
    public function getRelationInfo()
    {
        $info = new \Magento\Framework\Object();
        $info->setTable(
            'catalog_product_bundle_selection'
        )->setParentFieldName(
            'parent_product_id'
        )->setChildFieldName(
            'product_id'
        );
        return $info;
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
        return $this->_bundleSelection->getChildrenIds($parentId, $required);
    }

    /**
     * Retrieve parent ids array by required child
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        return $this->_bundleSelection->getParentIdsByChild($childId);
    }

    /**
     * Return product sku based on sku_type attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getSku($product)
    {
        $sku = parent::getSku($product);

        if ($product->getData('sku_type')) {
            return $sku;
        } else {
            $skuParts = array($sku);

            if ($product->hasCustomOptions()) {
                $customOption = $product->getCustomOption('bundle_selection_ids');
                $selectionIds = unserialize($customOption->getValue());
                if (!empty($selectionIds)) {
                    $selections = $this->getSelectionsByIds($selectionIds, $product);
                    foreach ($selections->getItems() as $selection) {
                        $skuParts[] = $selection->getSku();
                    }
                }
            }

            return implode('-', $skuParts);
        }
    }

    /**
     * Return product weight based on weight_type attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getWeight($product)
    {
        if ($product->getData('weight_type')) {
            return $product->getData('weight');
        } else {
            $weight = 0;

            if ($product->hasCustomOptions()) {
                $customOption = $product->getCustomOption('bundle_selection_ids');
                $selectionIds = unserialize($customOption->getValue());
                $selections = $this->getSelectionsByIds($selectionIds, $product);
                foreach ($selections->getItems() as $selection) {
                    $qtyOption = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
                    if ($qtyOption) {
                        $weight += $selection->getWeight() * $qtyOption->getValue();
                    } else {
                        $weight += $selection->getWeight();
                    }
                }
            }
            return $weight;
        }
    }

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('bundle_selection_ids');
            $selectionIds = unserialize($customOption->getValue());
            $selections = $this->getSelectionsByIds($selectionIds, $product);
            $virtualCount = 0;
            foreach ($selections->getItems() as $selection) {
                if ($selection->isVirtual()) {
                    $virtualCount++;
                }
            }
            if ($virtualCount == count($selections)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Before save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|void
     */
    public function beforeSave($product)
    {
        parent::beforeSave($product);

        // If bundle product has dynamic weight, than delete weight attribute
        if (!$product->getData('weight_type') && $product->hasData('weight')) {
            $product->setData('weight', false);
        }

        if ($product->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
            /** unset product custom options for dynamic price */
            if ($product->hasData('product_options')) {
                $product->unsetData('product_options');
            }
        }

        $product->canAffectOptions(false);

        if ($product->getCanSaveBundleSelections()) {
            $product->canAffectOptions(true);
            $selections = $product->getBundleSelectionsData();
            if ($selections && !empty($selections)) {
                $options = $product->getBundleOptionsData();
                if ($options) {
                    foreach ($options as $option) {
                        if (empty($option['delete']) || 1 != (int) $option['delete']) {
                            $product->setTypeHasOptions(true);
                            if (1 == (int) $option['required']) {
                                $product->setTypeHasRequiredOptions(true);
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function save($product)
    {
        parent::save($product);
        /* @var $resource \Magento\Bundle\Model\Resource\Bundle */
        $resource = $this->_bundleFactory->create();

        $options = $product->getBundleOptionsData();
        if ($options) {
            $product->setIsRelationsChanged(true);

            foreach ($options as $key => $option) {
                if (isset($option['option_id']) && $option['option_id'] == '') {
                    unset($option['option_id']);
                }

                $optionModel = $this->_bundleOption->create()
                    ->setData($option)
                    ->setParentId($product->getId())
                    ->setStoreId($product->getStoreId());

                $optionModel->isDeleted((bool) $option['delete']);
                $optionModel->save();
                $options[$key]['option_id'] = $optionModel->getOptionId();
            }

            $usedProductIds = array();
            $excludeSelectionIds = array();

            $selections = $product->getBundleSelectionsData();
            if ($selections) {
                foreach ($selections as $index => $group) {
                    foreach ($group as $selection) {
                        if (isset($selection['selection_id']) && $selection['selection_id'] == '') {
                            unset($selection['selection_id']);
                        }

                        if (!isset($selection['is_default'])) {
                            $selection['is_default'] = 0;
                        }

                        $selectionModel = $this->_bundleModelSelection->create()
                            ->setData($selection)
                            ->setOptionId($options[$index]['option_id'])
                            ->setWebsiteId($this->_storeManager->getStore($product->getStoreId())->getWebsiteId())
                            ->setParentProductId($product->getId());

                        $selectionModel->isDeleted((bool) $selection['delete']);
                        $selectionModel->save();

                        $selection['selection_id'] = $selectionModel->getSelectionId();

                        if ($selectionModel->getSelectionId()) {
                            $excludeSelectionIds[] = $selectionModel->getSelectionId();
                            $usedProductIds[] = $selectionModel->getProductId();
                        }
                    }
                }

                $resource->dropAllUnneededSelections($product->getId(), $excludeSelectionIds);
                $resource->saveProductRelations($product->getId(), array_unique($usedProductIds));
            }

            if ($product->getData('price_type') != $product->getOrigData('price_type')) {
                $resource->dropAllQuoteChildItems($product->getId());
            }
        }

        return $this;
    }

    /**
     * Retrieve bundle options items
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Framework\Object[]
     */
    public function getOptions($product)
    {
        return $this->getOptionsCollection($product)->getItems();
    }

    /**
     * Retrieve bundle options ids
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getOptionsIds($product)
    {
        return $this->getOptionsCollection($product)->getAllIds();
    }

    /**
     * Retrieve bundle option collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function getOptionsCollection($product)
    {
        if (!$product->hasData($this->_keyOptionsCollection)) {
            $optionsCollection = $this->_bundleOption->create()
                ->getResourceCollection()
                ->setProductIdFilter($product->getId())
                ->setPositionOrder();
            $storeId = $this->getStoreFilter($product);
            if ($storeId instanceof \Magento\Store\Model\Store) {
                $storeId = $storeId->getId();
            }

            $optionsCollection->joinValues($storeId);
            $product->setData($this->_keyOptionsCollection, $optionsCollection);
        }
        return $product->getData($this->_keyOptionsCollection);
    }

    /**
     * Retrieve bundle selections collection based on used options
     *
     * @param array $optionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\Resource\Selection\Collection
     */
    public function getSelectionsCollection($optionIds, $product)
    {
        $keyOptionIds = is_array($optionIds) ? implode('_', $optionIds) : '';
        $key = $this->_keySelectionsCollection . $keyOptionIds;
        if (!$product->hasData($key)) {
            $storeId = $product->getStoreId();
            $selectionsCollection = $this->_bundleCollection->create()
                ->addAttributeToSelect($this->_config->getProductAttributes())
                ->addAttributeToSelect('tax_class_id')   //used for calculation item taxes in Bundle with Dynamic Price
                ->setFlag('require_stock_items', true)
                ->setFlag('product_children', true)
                ->setPositionOrder()
                ->addStoreFilter($this->getStoreFilter($product))
                ->setStoreId($storeId)
                ->addFilterByRequiredOptions()
                ->setOptionIdsFilter($optionIds);

            if (!$this->_catalogData->isPriceGlobal() && $storeId) {
                $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
                $selectionsCollection->joinPrices($websiteId);
            }

            $product->setData($key, $selectionsCollection);
        }
        return $product->getData($key);
    }

    /**
     * Method is needed for specific actions to change given quote options values
     * according current product type logic
     * Example: the catalog inventory validation of decimal qty can change qty to int,
     * so need to change quote item qty option value too.
     *
     * @param   array           $options
     * @param   \Magento\Framework\Object   $option
     * @param   mixed           $value
     * @param   \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function updateQtyOption($options, \Magento\Framework\Object $option, $value, $product)
    {
        $optionProduct = $option->getProduct($product);
        $optionUpdateFlag = $option->getHasQtyOptionUpdate();
        $optionCollection = $this->getOptionsCollection($product);

        $selections = $this->getSelectionsCollection($optionCollection->getAllIds(), $product);

        foreach ($selections as $selection) {
            if ($selection->getProductId() == $optionProduct->getId()) {
                foreach ($options as &$option) {
                    if ($option->getCode() == 'selection_qty_' . $selection->getSelectionId()) {
                        if ($optionUpdateFlag) {
                            $option->setValue(intval($option->getValue()));
                        } else {
                            $option->setValue($value);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Prepare Quote Item Quantity
     *
     * @param mixed $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function prepareQuoteItemQty($qty, $product)
    {
        return intval($qty);
    }

    /**
     * Checking if we can sale this bundle
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        $salable = parent::isSalable($product);
        if (!is_null($salable)) {
            return $salable;
        }

        $optionCollection = $this->getOptionsCollection($product);

        if (!count($optionCollection->getItems())) {
            return false;
        }

        $requiredOptionIds = array();

        foreach ($optionCollection->getItems() as $option) {
            if ($option->getRequired()) {
                $requiredOptionIds[$option->getId()] = 0;
            }
        }

        $selectionCollection = $this->getSelectionsCollection($optionCollection->getAllIds(), $product);

        if (!count($selectionCollection->getItems())) {
            return false;
        }
        $salableSelectionCount = 0;
        foreach ($selectionCollection as $selection) {
            if ($selection->isSalable()) {
                $requiredOptionIds[$selection->getOptionId()] = 1;
                $salableSelectionCount++;
            }
        }

        return array_sum($requiredOptionIds) == count($requiredOptionIds) && $salableSelectionCount;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare of bundle selections options.
     *
     * @param \Magento\Framework\Object $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(\Magento\Framework\Object $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }

        $selections = array();
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        $skipSaleableCheck = $this->_catalogProduct->getSkipSaleableCheck();
        $_appendAllSelections = (bool) $product->getSkipCheckRequiredOption() || $skipSaleableCheck;

        $options = $buyRequest->getBundleOption();
        if (is_array($options)) {
            $options = array_filter($options, 'intval');
            $qtys = $buyRequest->getBundleOptionQty();
            foreach ($options as $_optionId => $_selections) {
                if (empty($_selections)) {
                    unset($options[$_optionId]);
                }
            }
            $optionIds = array_keys($options);

            if (empty($optionIds) && $isStrictProcessMode) {
                return __('Please specify product option(s).');
            }

            $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);
            $optionsCollection = $this->getOptionsCollection($product);
            if (!$product->getSkipCheckRequiredOption() && $isStrictProcessMode) {
                foreach ($optionsCollection->getItems() as $option) {
                    if ($option->getRequired() && !isset($options[$option->getId()])) {
                        return __('Please select all required options.');
                    }
                }
            }
            $selectionIds = array();

            foreach ($options as $selectionId) {
                if (!is_array($selectionId)) {
                    if ($selectionId != '') {
                        $selectionIds[] = (int) $selectionId;
                    }
                } else {
                    foreach ($selectionId as $id) {
                        if ($id != '') {
                            $selectionIds[] = (int) $id;
                        }
                    }
                }
            }
            // If product has not been configured yet then $selections array should be empty
            if (!empty($selectionIds)) {
                $selections = $this->getSelectionsByIds($selectionIds, $product);

                // Check if added selections are still on sale
                foreach ($selections->getItems() as $selection) {
                    if (!$selection->isSalable() && !$skipSaleableCheck) {
                        $_option = $optionsCollection->getItemById($selection->getOptionId());
                        if (is_array($options[$_option->getId()]) && count($options[$_option->getId()]) > 1) {
                            $moreSelections = true;
                        } else {
                            $moreSelections = false;
                        }
                        if ($_option->getRequired() && (!$_option->isMultiSelection() ||
                            $_option->isMultiSelection() && !$moreSelections)
                        ) {
                            return __('The required options you selected are not available.');
                        }
                    }
                }

                $optionsCollection->appendSelections($selections, false, $_appendAllSelections);

                $selections = $selections->getItems();
            } else {
                $selections = array();
            }
        } else {
            $product->setOptionsValidationFail(true);
            $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);

            $optionCollection = $product->getTypeInstance()->getOptionsCollection($product);
            $optionIds = $product->getTypeInstance()->getOptionsIds($product);
            $selectionCollection = $product->getTypeInstance()->getSelectionsCollection($optionIds, $product);
            $options = $optionCollection->appendSelections($selectionCollection, false, $_appendAllSelections);

            foreach ($options as $option) {
                if ($option->getRequired() && count($option->getSelections()) == 1) {
                    $selections = array_merge($selections, $option->getSelections());
                } else {
                    $selections = array();
                    break;
                }
            }
        }
        if (count($selections) > 0 || !$isStrictProcessMode) {
            $uniqueKey = array($product->getId());
            $selectionIds = array();

            // Shuffle selection array by option position
            usort($selections, array($this, 'shakeSelections'));

            foreach ($selections as $selection) {
                if ($selection->getSelectionCanChangeQty() && isset($qtys[$selection->getOptionId()])) {
                    $qty = (float) $qtys[$selection->getOptionId()] > 0 ? $qtys[$selection->getOptionId()] : 1;
                } else {
                    $qty = (float) $selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
                }
                $qty = (float) $qty;

                $product->addCustomOption('selection_qty_' . $selection->getSelectionId(), $qty, $selection);
                $selection->addCustomOption('selection_id', $selection->getSelectionId());

                $beforeQty = 0;
                $customOption = $product->getCustomOption('product_qty_' . $selection->getId());
                if ($customOption && $customOption->getProduct()->getId() == $selection->getId()) {
                    $beforeQty = (float) $customOption->getValue();
                }
                $product->addCustomOption('product_qty_' . $selection->getId(), $qty + $beforeQty, $selection);

                /*
                 * Create extra attributes that will be converted to product options in order item
                 * for selection (not for all bundle)
                 */
                $price = $product->getPriceModel()->getSelectionFinalTotalPrice($product, $selection, 0, $qty);
                $attributes = array(
                    'price' => $this->priceCurrency->convert($price),
                    'qty' => $qty,
                    'option_label' => $selection->getOption()->getTitle(),
                    'option_id' => $selection->getOption()->getId()
                );

                $_result = $selection->getTypeInstance()->prepareForCart($buyRequest, $selection);
                if (is_string($_result) && !is_array($_result)) {
                    return $_result;
                }

                if (!isset($_result[0])) {
                    return __('We cannot add this item to your shopping cart.');
                }

                $result[] = $_result[0]->setParentProductId($product->getId())
                    ->addCustomOption('bundle_option_ids', serialize(array_map('intval', $optionIds)))
                    ->addCustomOption('bundle_selection_attributes', serialize($attributes));

                if ($isStrictProcessMode) {
                    $_result[0]->setCartQty($qty);
                }

                $selectionIds[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $qty;
            }

            // "unique" key for bundle selection and add it to selections and bundle for selections
            $uniqueKey = implode('_', $uniqueKey);
            foreach ($result as $item) {
                $item->addCustomOption('bundle_identity', $uniqueKey);
            }
            $product->addCustomOption('bundle_option_ids', serialize(array_map('intval', $optionIds)));
            $product->addCustomOption('bundle_selection_ids', serialize($selectionIds));

            return $result;
        }

        return $this->getSpecifyOptionMessage();
    }

    /**
     * Retrieve message for specify option(s)
     *
     * @return string
     */
    public function getSpecifyOptionMessage()
    {
        return __('Please specify product option(s).');
    }

    /**
     * Retrieve bundle selections collection based on ids
     *
     * @param array $selectionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\Resource\Selection\Collection
     */
    public function getSelectionsByIds($selectionIds, $product)
    {
        sort($selectionIds);

        $usedSelections = $product->getData($this->_keyUsedSelections);
        $usedSelectionsIds = $product->getData($this->_keyUsedSelectionsIds);

        if (!$usedSelections || serialize($usedSelectionsIds) != serialize($selectionIds)) {
            $storeId = $product->getStoreId();
            $usedSelections = $this->_bundleCollection
                ->create()
                ->addAttributeToSelect('*')
                ->setFlag('require_stock_items', true)
                ->setFlag('product_children', true)
                ->addStoreFilter($this->getStoreFilter($product))
                ->setStoreId($storeId)
                ->setPositionOrder()
                ->addFilterByRequiredOptions()
                ->setSelectionIdsFilter($selectionIds);

            if (!$this->_catalogData->isPriceGlobal() && $storeId) {
                $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
                $usedSelections->joinPrices($websiteId);
            }
            $product->setData($this->_keyUsedSelections, $usedSelections);
            $product->setData($this->_keyUsedSelectionsIds, $selectionIds);
        }
        return $usedSelections;
    }

    /**
     * Retrieve bundle options collection based on ids
     *
     * @param array $optionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function getOptionsByIds($optionIds, $product)
    {
        sort($optionIds);

        $usedOptions = $product->getData($this->_keyUsedOptions);
        $usedOptionsIds = $product->getData($this->_keyUsedOptionsIds);

        if (!$usedOptions || serialize($usedOptionsIds) != serialize($optionIds)) {
            $usedOptions = $this->_bundleOption
                ->create()
                ->getResourceCollection()
                ->setProductIdFilter($product->getId())
                ->setPositionOrder()
                ->joinValues($this->_storeManager->getStore()->getId())
                ->setIdFilter($optionIds);
            $product->setData($this->_keyUsedOptions, $usedOptions);
            $product->setData($this->_keyUsedOptionsIds, $optionIds);
        }
        return $usedOptions;
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
        $optionArr = parent::getOrderOptions($product);
        $bundleOptions = array();

        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('bundle_option_ids');
            $optionIds = unserialize($customOption->getValue());
            $options = $this->getOptionsByIds($optionIds, $product);
            $customOption = $product->getCustomOption('bundle_selection_ids');
            $selectionIds = unserialize($customOption->getValue());
            $selections = $this->getSelectionsByIds($selectionIds, $product);
            foreach ($selections->getItems() as $selection) {
                if ($selection->isSalable()) {
                    $selectionQty = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
                    if ($selectionQty) {
                        $price = $product->getPriceModel()->getSelectionFinalTotalPrice(
                            $product,
                            $selection,
                            0,
                            $selectionQty->getValue()
                        );

                        $option = $options->getItemById($selection->getOptionId());
                        if (!isset($bundleOptions[$option->getId()])) {
                            $bundleOptions[$option->getId()] = array(
                                'option_id' => $option->getId(),
                                'label' => $option->getTitle(),
                                'value' => array()
                            );
                        }

                        $bundleOptions[$option->getId()]['value'][] = array(
                            'title' => $selection->getName(),
                            'qty' => $selectionQty->getValue(),
                            'price' => $this->priceCurrency->convert($price)
                        );
                    }
                }
            }
        }

        $optionArr['bundle_options'] = $bundleOptions;

        /**
         * Product Prices calculations save
         */
        if ($product->getPriceType()) {
            $optionArr['product_calculations'] = self::CALCULATE_PARENT;
        } else {
            $optionArr['product_calculations'] = self::CALCULATE_CHILD;
        }

        $optionArr['shipment_type'] = $product->getShipmentType();

        return $optionArr;
    }

    /**
     * Sort selections method for usort function
     * Sort selections by option position, selection position and selection id
     *
     * @param  \Magento\Catalog\Model\Product $firstItem
     * @param  \Magento\Catalog\Model\Product $secondItem
     * @return int
     */
    public function shakeSelections($firstItem, $secondItem)
    {
        $aPosition = array(
            $firstItem->getOption()->getPosition(),
            $firstItem->getOptionId(),
            $firstItem->getPosition(),
            $firstItem->getSelectionId()
        );
        $bPosition = array(
            $secondItem->getOption()->getPosition(),
            $secondItem->getOptionId(),
            $secondItem->getPosition(),
            $secondItem->getSelectionId()
        );
        if ($aPosition == $bPosition) {
            return 0;
        } else {
            return $aPosition < $bPosition ? -1 : 1;
        }
    }

    /**
     * Return true if product has options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasOptions($product)
    {
        $this->setStoreFilter($product->getStoreId(), $product);
        $optionIds = $this->getOptionsCollection($product)->getAllIds();
        $collection = $this->getSelectionsCollection($optionIds, $product);

        if (count($collection) > 0 || $product->getOptions()) {
            return true;
        }

        return false;
    }

    /**
     * Allow for updates of children qty's
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean true
     */
    public function getForceChildItemQtyChanges($product)
    {
        return true;
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
        $searchData = parent::getSearchableData($product);

        $optionSearchData = $this->_bundleOption->create()->getSearchableData(
            $product->getId(),
            $product->getStoreId()
        );
        if ($optionSearchData) {
            $searchData = array_merge($searchData, $optionSearchData);
        }

        return $searchData;
    }

    /**
     * Check if product can be bought
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function checkProductBuyState($product)
    {
        parent::checkProductBuyState($product);
        $productOptionIds = $this->getOptionsIds($product);
        $productSelections = $this->getSelectionsCollection($productOptionIds, $product);
        $selectionIds = $product->getCustomOption('bundle_selection_ids');
        $selectionIds = unserialize($selectionIds->getValue());
        $buyRequest = $product->getCustomOption('info_buyRequest');
        $buyRequest = new \Magento\Framework\Object(unserialize($buyRequest->getValue()));
        $bundleOption = $buyRequest->getBundleOption();

        if (empty($bundleOption)) {
            throw new \Magento\Framework\Model\Exception($this->getSpecifyOptionMessage());
        }

        $skipSaleableCheck = $this->_catalogProduct->getSkipSaleableCheck();
        foreach ($selectionIds as $selectionId) {
            /* @var $selection \Magento\Bundle\Model\Selection */
            $selection = $productSelections->getItemById($selectionId);
            if (!$selection || !$selection->isSalable() && !$skipSaleableCheck) {
                throw new \Magento\Framework\Model\Exception(__('The required options you selected are not available.'));
            }
        }

        $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);
        $optionsCollection = $this->getOptionsCollection($product);
        foreach ($optionsCollection->getItems() as $option) {
            if ($option->getRequired() && empty($bundleOption[$option->getId()])) {
                throw new \Magento\Framework\Model\Exception(__('Please select all required options.'));
            }
        }

        return $this;
    }

    /**
     * Retrieve products divided into groups required to purchase
     * At least one product in each group has to be purchased
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductsToPurchaseByReqGroups($product)
    {
        $groups = array();
        $allProducts = array();
        $hasRequiredOptions = false;
        foreach ($this->getOptions($product) as $option) {
            $groupProducts = array();
            foreach ($this->getSelectionsCollection(array($option->getId()), $product) as $childProduct) {
                $groupProducts[] = $childProduct;
                $allProducts[] = $childProduct;
            }
            if ($option->getRequired()) {
                $groups[] = $groupProducts;
                $hasRequiredOptions = true;
            }
        }
        if (!$hasRequiredOptions) {
            $groups = array($allProducts);
        }
        return $groups;
    }

    /**
     * Prepare selected options for bundle product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Framework\Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $option = $buyRequest->getBundleOption();
        $optionQty = $buyRequest->getBundleOptionQty();

        $option = is_array($option) ? array_filter($option, 'intval') : array();
        $optionQty = is_array($optionQty) ? array_filter($optionQty, 'intval') : array();

        $options = array('bundle_option' => $option, 'bundle_option_qty' => $optionQty);

        return $options;
    }

    /**
     * Check if product can be configured
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function canConfigure($product)
    {
        return $product instanceof \Magento\Catalog\Model\Product && $product->isAvailable() && parent::canConfigure(
            $product
        );
    }

    /**
     * Delete data specific for Bundle product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }

    /**
     * Return array of specific to type product entities
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getIdentities(\Magento\Catalog\Model\Product $product)
    {
        $identities = parent::getIdentities($product);
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($this->getOptions($product) as $option) {
            if ($option->getSelections()) {
                /** @var \Magento\Catalog\Model\Product $selection */
                foreach ($option->getSelections() as $selection) {
                    $identities = array_merge($identities, $selection->getIdentities());
                }
            }
        }
        return $identities;
    }
}
