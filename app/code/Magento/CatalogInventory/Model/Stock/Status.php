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
namespace Magento\CatalogInventory\Model\Stock;

use Magento\CatalogInventory\Model\Stock;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

/**
 * CatalogInventory Stock Status per website Model
 *
 * @method int getProductId()
 * @method Status setProductId(int $value)
 * @method int getWebsiteId()
 * @method Status setWebsiteId(int $value)
 * @method int getStockId()
 * @method Status setStockId(int $value)
 * @method float getQty()
 * @method Status setQty(float $value)
 * @method int getStockStatus()
 * @method Status setStockStatus(int $value)
 */
class Status extends \Magento\Framework\Model\AbstractModel
{
    /**#@+
     * Stock Status values
     */
    const STATUS_OUT_OF_STOCK = 0;

    const STATUS_IN_STOCK = 1;
    /**#@-*/

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $_productTypes = array();

    /**
     * Websites cache
     *
     * @var array
     */
    protected $_websites;

    /**
     * Catalog inventory data
     *
     * @var \Magento\CatalogInventory\Helper\Data
     */
    protected $_catalogInventoryData;

    /**
     * @var Type
     */
    protected $_productType;

    /**
     * Store model manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Stock item factory
     *
     * @var ItemFactory
     */
    protected $_stockItemFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Type $productType
     * @param \Magento\Catalog\Model\Product\Website $productWebsite
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param ItemFactory $stockItemFactory
     * @param \Magento\CatalogInventory\Helper\Data $catalogInventoryData
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Type $productType,
        \Magento\Catalog\Model\Product\Website $productWebsite,
        \Magento\Framework\StoreManagerInterface $storeManager,
        ItemFactory $stockItemFactory,
        \Magento\CatalogInventory\Helper\Data $catalogInventoryData,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_catalogInventoryData = $catalogInventoryData;
        $this->_productType = $productType;
        $this->_productWebsite = $productWebsite;
        $this->_storeManager = $storeManager;
        $this->_stockItemFactory = $stockItemFactory;
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CatalogInventory\Model\Resource\Stock\Status');
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    public function getProductTypeInstances()
    {
        if (empty($this->_productTypes)) {
            $productEmulator = new \Magento\Framework\Object();

            foreach (array_keys($this->_productType->getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->_productTypes[$typeId] = $this->_productType->factory($productEmulator);
            }
        }
        return $this->_productTypes;
    }

    /**
     * Retrieve Product Type Instance By Product Type
     *
     * @param string $productType
     * @return AbstractType|bool
     */
    public function getProductTypeInstance($productType)
    {
        $types = $this->getProductTypeInstances();
        if (isset($types[$productType])) {
            return $types[$productType];
        }
        return false;
    }

    /**
     * Retrieve website models
     *
     * @param int|null $websiteId
     * @return array
     */
    public function getWebsites($websiteId = null)
    {
        if (is_null($this->_websites)) {
            /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status $resource */
            $resource = $this->getResource();
            $this->_websites = $resource->getWebsiteStores();
        }

        $websites = $this->_websites;
        if (!is_null($websiteId) && isset($this->_websites[$websiteId])) {
            $websites = array($websiteId => $this->_websites[$websiteId]);
        }

        return $websites;
    }

    /**
     * Assign Stock Status to Product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $stockId
     * @param int $stockStatus
     * @return $this
     */
    public function assignProduct(
        \Magento\Catalog\Model\Product $product,
        $stockId = Stock::DEFAULT_STOCK_ID,
        $stockStatus = null
    ) {
        if (is_null($stockStatus)) {
            $websiteId = $product->getStore()->getWebsiteId();
            $status = $this->getProductStockStatus($product->getId(), $websiteId, $stockId);
            $stockStatus = isset($status[$product->getId()]) ? $status[$product->getId()] : null;
        }

        $product->setIsSalable($stockStatus);

        return $this;
    }

    /**
     * Rebuild stock status for all products
     *
     * @param int $websiteId
     * @return $this
     */
    public function rebuild($websiteId = null)
    {
        $lastProductId = 0;
        while (true) {
            /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status $resource */
            $resource = $this->getResource();
            $productCollection = $resource->getProductCollection($lastProductId);
            if (!$productCollection) {
                break;
            }

            foreach ($productCollection as $productId => $productType) {
                $lastProductId = $productId;
                $this->updateStatus($productId, $productType, $websiteId);
            }
        }

        return $this;
    }

    /**
     * Update product status from stock item
     *
     * @param int $productId
     * @param string $productType
     * @param int $websiteId
     * @return $this
     */
    public function updateStatus($productId, $productType = null, $websiteId = null)
    {
        if (is_null($productType)) {
            $productType = $this->getProductType($productId);
        }

        /** @var Item $item */
        $item = $this->_stockItemFactory->create()->loadByProduct($productId);

        $status = self::STATUS_IN_STOCK;
        $qty = 0;
        if ($item->getId()) {
            $status = $item->getIsInStock();
            $qty = $item->getQty();
        }

        $this->_processChildren($productId, $productType, $qty, $status, $item->getStockId(), $websiteId);
        $this->_processParents($productId, $item->getStockId(), $websiteId);

        return $this;
    }

    /**
     * Process children stock status
     *
     * @param int $productId
     * @param string $productType
     * @param int $qty
     * @param int $status
     * @param int $stockId
     * @param int $websiteId
     * @return $this
     */
    protected function _processChildren(
        $productId,
        $productType,
        $qty = 0,
        $status = self::STATUS_IN_STOCK,
        $stockId = Stock::DEFAULT_STOCK_ID,
        $websiteId = null
    ) {
        if ($status == self::STATUS_OUT_OF_STOCK) {
            $this->saveProductStatus($productId, $status, $qty, $stockId, $websiteId);
            return $this;
        }

        $statuses = array();
        $websites = $this->getWebsites($websiteId);

        foreach (array_keys($websites) as $websiteId) {
            /* @var $website \Magento\Store\Model\Website */
            $statuses[$websiteId] = $status;
        }

        $typeInstance = $this->getProductTypeInstance($productType);
        if (!$typeInstance) {
            return $this;
        }

        $requiredChildrenIds = $typeInstance->getChildrenIds($productId, true);
        if ($requiredChildrenIds) {
            $childrenIds = array();
            foreach ($requiredChildrenIds as $groupedChildrenIds) {
                $childrenIds = array_merge($childrenIds, $groupedChildrenIds);
            }
            $childrenWebsites = $this->_productWebsite->getWebsites($childrenIds);
            foreach ($websites as $websiteId => $storeId) {
                $childrenStatus = $this->getProductStatus($childrenIds, $storeId);
                $childrenStock = $this->getProductStockStatus($childrenIds, $websiteId, $stockId);
                $websiteStatus = $statuses[$websiteId];
                foreach ($requiredChildrenIds as $groupedChildrenIds) {
                    $optionStatus = false;
                    foreach ($groupedChildrenIds as $childId) {
                        if (isset($childrenStatus[$childId])
                            && isset($childrenWebsites[$childId])
                            && in_array($websiteId, $childrenWebsites[$childId])
                            && $childrenStatus[$childId] == ProductStatus::STATUS_ENABLED
                            && isset($childrenStock[$childId])
                            && $childrenStock[$childId] == self::STATUS_IN_STOCK
                        ) {
                            $optionStatus = true;
                        }
                    }
                    $websiteStatus = $websiteStatus && $optionStatus;
                }
                $statuses[$websiteId] = (int) $websiteStatus;
            }
        }

        foreach ($statuses as $websiteId => $websiteStatus) {
            $this->saveProductStatus($productId, $websiteStatus, $qty, $stockId, $websiteId);
        }

        return $this;
    }

    /**
     * Process Parents by child
     *
     * @param int $productId
     * @param int $stockId
     * @param int $websiteId
     * @return $this
     */
    protected function _processParents($productId, $stockId = Stock::DEFAULT_STOCK_ID, $websiteId = null)
    {
        $parentIds = array();
        foreach ($this->getProductTypeInstances() as $typeInstance) {
            /* @var $typeInstance AbstractType */
            $parentIds = array_merge($parentIds, $typeInstance->getParentIdsByChild($productId));
        }

        if (!$parentIds) {
            return $this;
        }

        $productTypes = $this->getProductsType($parentIds);
        /** @var Item $item */
        $item = $this->_stockItemFactory->create();

        foreach ($parentIds as $parentId) {
            $parentType = isset($productTypes[$parentId]) ? $productTypes[$parentId] : null;
            $item->setData(array('stock_id' => $stockId))->setOrigData()->loadByProduct($parentId);
            $status = self::STATUS_IN_STOCK;
            $qty = 0;
            if ($item->getId()) {
                $status = $item->getIsInStock();
                $qty = $item->getQty();
            }

            $this->_processChildren($parentId, $parentType, $qty, $status, $item->getStockId(), $websiteId);
        }

        return $this;
    }

    /**
     * Save product status per website
     * if website is null, saved for all websites
     *
     * @param int $productId
     * @param int $status
     * @param int $qty
     * @param int $stockId
     * @param int|null $websiteId
     * @return $this
     */
    public function saveProductStatus(
        $productId,
        $status,
        $qty = 0,
        $stockId = Stock::DEFAULT_STOCK_ID,
        $websiteId = null
    ) {
        /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status $resource */
        $resource = $this->getResource();
        $resource->saveProductStatus($this, $productId, $status, $qty, $stockId, $websiteId);
        return $this;
    }

    /**
     * Retrieve Product(s) stock status
     *
     * @param int[] $productIds
     * @param int $websiteId
     * @param int $stockId
     * @return array
     */
    public function getProductStockStatus($productIds, $websiteId, $stockId = Stock::DEFAULT_STOCK_ID)
    {
        /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status $resource */
        $resource = $this->getResource();
        return $resource->getProductStockStatus($productIds, $websiteId, $stockId);
    }

    /**
     * Retrieve Product(s) status
     *
     * @param int|int[] $productIds
     * @param int $storeId
     * @return array
     */
    public function getProductStatus($productIds, $storeId = null)
    {
        /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status $resource */
        $resource = $this->getResource();
        return $resource->getProductStatus($productIds, $storeId);
    }

    /**
     * Retrieve Product Type
     *
     * @param int $productId
     * @return string|false
     */
    public function getProductType($productId)
    {
        /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status $resource */
        $resource = $this->getResource();
        $types = $resource->getProductsType($productId);
        if (isset($types[$productId])) {
            return $types[$productId];
        }
        return false;
    }

    /**
     * Retrieve Products Type as array
     * Return array as key product_id, value type
     *
     * @param array|int $productIds
     * @return array
     */
    public function getProductsType($productIds)
    {
        /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status $resource */
        $resource = $this->getResource();
        return $resource->getProductsType($productIds);
    }

    /**
     * Add information about stock status to product collection
     *
     * @param   \Magento\Catalog\Model\Resource\Product\Collection $productCollection
     * @param   int|null $websiteId
     * @param   int|null $stockId
     * @return  $this
     */
    public function addStockStatusToProducts($productCollection, $websiteId = null, $stockId = null)
    {
        if ($stockId === null) {
            $stockId = Stock::DEFAULT_STOCK_ID;
        }
        if ($websiteId === null) {
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            if ((int) $websiteId == 0 && $productCollection->getStoreId()) {
                $websiteId = $this->_storeManager->getStore($productCollection->getStoreId())->getWebsiteId();
            }
        }
        $productIds = array();
        foreach ($productCollection as $product) {
            $productIds[] = $product->getId();
        }

        if (!empty($productIds)) {
            $stockStatuses = $this->getProductStockStatus($productIds, $websiteId, $stockId);
            foreach ($stockStatuses as $productId => $status) {
                if ($product = $productCollection->getItemById($productId)) {
                    $product->setIsSalable($status);
                }
            }
        }

        return $this;
    }

    /**
     * Add stock status to prepare index select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Store\Model\Website $website
     * @return $this
     */
    public function addStockStatusToSelect(\Magento\Framework\DB\Select $select, \Magento\Store\Model\Website $website)
    {
        $resource = $this->_getResource();
        $resource->addStockStatusToSelect($select, $website);
        return $this;
    }

    /**
     * Add only is in stock products filter to product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @return $this
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $resource = $this->_getResource();
        $resource->addIsInStockFilterToCollection($collection);
        return $this;
    }

    /**
     * Get options for stock attribute in product creation
     *
     * @return array
     */
    public static function getAllOptions()
    {
        return array(
            array('value' => Stock::STOCK_IN_STOCK, 'label' => __('In Stock')),
            array('value' => Stock::STOCK_OUT_OF_STOCK, 'label' => __('Out of Stock'))
        );
    }
}
