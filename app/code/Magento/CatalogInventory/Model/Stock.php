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
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Model\Stock\Item;

/**
 * Stock model
 *
 * @method string getStockName()
 * @method \Magento\CatalogInventory\Model\Stock setStockName(string $value)
 */
class Stock extends \Magento\Framework\Model\AbstractModel
{
    const BACKORDERS_NO = 0;

    const BACKORDERS_YES_NONOTIFY = 1;

    const BACKORDERS_YES_NOTIFY = 2;

    const STOCK_OUT_OF_STOCK = 0;

    const STOCK_IN_STOCK = 1;

    /**
     * Default stock id
     */
    const DEFAULT_STOCK_ID = 1;

    /**
     * @var \Magento\CatalogInventory\Service\V1\StockItemService
     */
    protected $stockItemService;

    /**
     * @var Stock\Status
     */
    protected $stockStatus;

    /**
     * Store model manager
     *
     * @var \Magento\Framework\StoreManagerInterface
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
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Resource\Stock\Item\CollectionFactory $collectionFactory
     * @param \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService
     * @param Stock\Status $stockStatus
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param Stock\ItemFactory $stockItemFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Model\Resource\Stock\Item\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService,
        \Magento\CatalogInventory\Model\Stock\Status $stockStatus,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_collectionFactory = $collectionFactory;
        $this->stockItemService = $stockItemService;
        $this->stockStatus = $stockStatus;
        $this->_storeManager = $storeManager;
        $this->_stockItemFactory = $stockItemFactory;
        $this->productFactory = $productFactory;
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
        $items = $this->getItemCollection()->addProductsFilter($productCollection)
            ->joinStockStatus($productCollection->getStoreId())
            ->load();
        $stockItems = array();
        foreach ($items as $item) {
            $stockItems[$item->getProductId()] = $item;
        }
        foreach ($productCollection as $product) {
            if (isset($stockItems[$product->getId()])) {
                $this->stockStatus->assignProduct(
                    $product,
                    $stockItems[$product->getId()]->getStockId(),
                    $product->getStockStatus()
                );
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
     * Get Product type
     *
     * @param int $productId
     * @return string
     */
    protected function getProductType($productId)
    {
        $product = $this->productFactory->create();
        $product->load($productId);
        return $product->getTypeId();
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
            if ($canSubtractQty && $this->stockItemService->isQty($this->getProductType($productId))) {
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
     * @throws \Magento\Framework\Model\Exception
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
                throw new \Magento\Framework\Model\Exception(
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
     * @param  \Magento\Framework\Object $item
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function registerItemSale(\Magento\Framework\Object $item)
    {
        $productId = $item->getProductId();
        if (!$productId) {
            throw new \Magento\Framework\Model\Exception(
                __('We cannot specify a product identifier for the order item.')
            );
        }
        /** @var Item $stockItem */
        $stockItem = $this->_stockItemFactory->create()->loadByProduct($productId);
        if ($this->stockItemService->isQty($this->getProductType($productId))) {
            if ($item->getStoreId()) {
                $stockItem->setStoreId($item->getStoreId());
            }
            if ($stockItem->checkQty($item->getQtyOrdered())) {
                $stockItem->subtractQty($item->getQtyOrdered());
                $stockItem->save();
            }
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
        if ($stockItem->getId() && $this->stockItemService->isQty($this->getProductType($productId))) {
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
