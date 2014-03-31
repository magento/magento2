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
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Model\Stock\Item;

/**
 * Stock model
 *
 * @method \Magento\CatalogInventory\Model\Resource\Stock _getResource()
 * @method \Magento\CatalogInventory\Model\Resource\Stock getResource()
 * @method string getStockName()
 * @method \Magento\CatalogInventory\Model\Stock setStockName(string $value)
 */
class Stock extends \Magento\Model\AbstractModel
{
    const BACKORDERS_NO = 0;

    const BACKORDERS_YES_NONOTIFY = 1;

    const BACKORDERS_YES_NOTIFY = 2;

    const STOCK_OUT_OF_STOCK = 0;

    const STOCK_IN_STOCK = 1;

    const DEFAULT_STOCK_ID = 1;

    /**
     * Catalog inventory data
     *
     * @var \Magento\CatalogInventory\Helper\Data
     */
    protected $_catalogInventoryData;

    /**
     * Store model manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Stock item factory
     *
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $_stockItemFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\Item\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\CatalogInventory\Model\Resource\Stock\Item\CollectionFactory $collectionFactory
     * @param \Magento\CatalogInventory\Helper\Data $catalogInventoryData
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\CatalogInventory\Model\Resource\Stock\Item\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Helper\Data $catalogInventoryData,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_collectionFactory = $collectionFactory;
        $this->_catalogInventoryData = $catalogInventoryData;
        $this->_storeManager = $storeManager;
        $this->_stockItemFactory = $stockItemFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CatalogInventory\Model\Resource\Stock');
    }

    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getId()
    {
        return self::DEFAULT_STOCK_ID;
    }

    /**
     * Add stock item objects to products
     *
     * @param array $productCollection
     * @return $this
     */
    public function addItemsToProducts($productCollection)
    {
        $items = $this->getItemCollection()->addProductsFilter(
            $productCollection
        )->joinStockStatus(
            $productCollection->getStoreId()
        )->load();
        $stockItems = array();
        foreach ($items as $item) {
            $stockItems[$item->getProductId()] = $item;
        }
        foreach ($productCollection as $product) {
            if (isset($stockItems[$product->getId()])) {
                $stockItems[$product->getId()]->assignProduct($product);
            }
        }
        return $this;
    }

    /**
     * Retrieve items collection object with stock filter
     *
     * @return \Magento\CatalogInventory\Model\Resource\Stock\Item\Collection
     */
    public function getItemCollection()
    {
        return $this->_collectionFactory->create()->addStockFilter($this->getId());
    }

    /**
     * Prepare array($productId=>$qty) based on array($productId => array('qty'=>$qty, 'item'=>$stockItem))
     *
     * @param array $items
     * @return array
     */
    protected function _prepareProductQtys($items)
    {
        $qtys = array();
        foreach ($items as $productId => $item) {
            if (empty($item['item'])) {
                $stockItem = $this->_stockItemFactory->create()->loadByProduct($productId);
            } else {
                $stockItem = $item['item'];
            }
            $canSubtractQty = $stockItem->getId() && $stockItem->canSubtractQty();
            if ($canSubtractQty && $this->_catalogInventoryData->isQty($stockItem->getTypeId())) {
                $qtys[$productId] = $item['qty'];
            }
        }
        return $qtys;
    }

    /**
     * Subtract product qtys from stock.
     * Return array of items that require full save
     *
     * @param array $items
     * @return Item[]
     * @throws \Magento\Model\Exception
     */
    public function registerProductsSale($items)
    {
        $qtys = $this->_prepareProductQtys($items);
        /** @var Item $item */
        $item = $this->_stockItemFactory->create();
        $this->_getResource()->beginTransaction();
        $stockInfo = $this->_getResource()->getProductsStock($this, array_keys($qtys), true);
        $fullSaveItems = array();
        foreach ($stockInfo as $itemInfo) {
            $item->setData($itemInfo);
            if (!$item->checkQty($qtys[$item->getProductId()])) {
                $this->_getResource()->commit();
                throw new \Magento\Model\Exception(
                    __('Not all of your products are available in the requested quantity.')
                );
            }
            $item->subtractQty($qtys[$item->getProductId()]);
            if (!$item->verifyStock() || $item->verifyNotification()) {
                $fullSaveItems[] = clone $item;
            }
        }
        $this->_getResource()->correctItemsQty($this, $qtys, '-');
        $this->_getResource()->commit();
        return $fullSaveItems;
    }

    /**
     * @param array $items
     * @return $this
     */
    public function revertProductsSale($items)
    {
        $qtys = $this->_prepareProductQtys($items);
        $this->_getResource()->correctItemsQty($this, $qtys, '+');
        return $this;
    }

    /**
     * Subtract ordered qty for product
     *
     * @param  \Magento\Object $item
     * @return $this
     * @throws \Magento\Model\Exception
     */
    public function registerItemSale(\Magento\Object $item)
    {
        $productId = $item->getProductId();
        if ($productId) {
            /** @var Item $stockItem */
            $stockItem = $this->_stockItemFactory->create()->loadByProduct($productId);
            if ($this->_catalogInventoryData->isQty($stockItem->getTypeId())) {
                if ($item->getStoreId()) {
                    $stockItem->setStoreId($item->getStoreId());
                }
                if ($stockItem->checkQty($item->getQtyOrdered())) {
                    $stockItem->subtractQty($item->getQtyOrdered());
                    $stockItem->save();
                }
            }
        } else {
            throw new \Magento\Model\Exception(__('We cannot specify a product identifier for the order item.'));
        }
        return $this;
    }

    /**
     * Get back to stock (when order is canceled or whatever else)
     *
     * @param int $productId
     * @param int|float $qty
     * @return $this
     */
    public function backItemQty($productId, $qty)
    {
        /** @var Item $stockItem */
        $stockItem = $this->_stockItemFactory->create()->loadByProduct($productId);
        if ($stockItem->getId() && $this->_catalogInventoryData->isQty($stockItem->getTypeId())) {
            $stockItem->addQty($qty);
            if ($stockItem->getCanBackInStock() && $stockItem->getQty() > $stockItem->getMinQty()) {
                $stockItem->setIsInStock(true)->setStockStatusChangedAutomaticallyFlag(true);
            }
            $stockItem->save();
        }
        return $this;
    }

    /**
     * Lock stock items for product ids array
     *
     * @param int|int[] $productIds
     * @return $this
     */
    public function lockProductItems($productIds)
    {
        $this->_getResource()->lockProductItems($this, $productIds);
        return $this;
    }

    /**
     * Adds filtering for collection to return only in stock products
     *
     * @param \Magento\Catalog\Model\Resource\Product\Link\Product\Collection $collection
     * @return $this
     */
    public function addInStockFilterToCollection($collection)
    {
        $this->getResource()->setInStockFilterToCollection($collection);
        return $this;
    }
}
