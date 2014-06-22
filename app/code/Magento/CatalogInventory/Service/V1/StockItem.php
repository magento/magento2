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
namespace Magento\CatalogInventory\Service\V1;

/**
 * Stock item service
 */
class StockItem implements StockItemInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemRegistry
     */
    protected $stockItemRegistry;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $config;

    /**
     * All product types registry in scope of quantity availability
     *
     * @var array
     */
    protected $isQtyTypeIds;

    /**
     * @var Data\StockItemBuilder
     */
    protected $stockItemBuilder;

    /**
     * @param \Magento\CatalogInventory\Model\Stock\ItemRegistry $stockItemRegistry
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
     * @param Data\StockItemBuilder $stockItemBuilder
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Stock\ItemRegistry $stockItemRegistry,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $config,
        Data\StockItemBuilder $stockItemBuilder
    ) {
        $this->stockItemRegistry = $stockItemRegistry;
        $this->config = $config;
        $this->stockItemBuilder = $stockItemBuilder;
    }

    /**
     * @param int $productId
     * @return \Magento\CatalogInventory\Service\V1\Data\StockItem
     */
    public function getStockItem($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        $this->stockItemBuilder->populateWithArray($stockItem->getData());
        return $this->stockItemBuilder->create();
    }

    /**
     * @param \Magento\CatalogInventory\Service\V1\Data\StockItem $stockItemDo
     * @return $this
     */
    public function saveStockItem($stockItemDo)
    {
        $stockItem = $this->stockItemRegistry->retrieve($stockItemDo->getProductId());
        $stockItem->setData($stockItemDo->__toArray());
        $stockItem->save();
        return $this;
    }

    /**
     * @param int $productId
     * @param int $qty
     * @return $this
     */
    public function subtractQty($productId, $qty)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        $stockItem->subtractQty($qty);
        return $this;
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function canSubtractQty($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->canSubtractQty();
    }

    /**
     * @param int $productId
     * @param int $qty
     * @return $this
     */
    public function addQty($productId, $qty)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        $stockItem->addQty($qty);
        return $this;
    }

    /**
     * @param int $productId
     * @return int
     */
    public function getMinQty($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getMinQty();
    }

    /**
     * @param int $productId
     * @return int
     */
    public function getMinSaleQty($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getMinSaleQty();
    }

    /**
     * @param int $productId
     * @return int
     */
    public function getMaxSaleQty($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getMaxSaleQty();
    }

    /**
     * @param int $productId
     * @return float
     */
    public function getNotifyStockQty($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getNotifyStockQty();
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function getEnableQtyIncrements($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getEnableQtyIncrements();
    }

    /**
     * @param int $productId
     * @return int
     */
    public function getQtyIncrements($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getQtyIncrements();
    }

    /**
     * @param int $productId
     * @return int
     */
    public function getBackorders($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getBackorders();
    }

    /**
     * @param int $productId
     * @return int
     */
    public function getManageStock($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getManageStock();
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function getCanBackInStock($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getCanBackInStock();
    }

    /**
     * @param int $productId
     * @param int $qty
     * @return bool
     */
    public function checkQty($productId, $qty)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->checkQty($qty);
    }

    /**
     * @param int $productId
     * @param int $qty
     * @return bool
     */
    public function suggestQty($productId, $qty)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->suggestQty($qty);
    }

    /**
     * @param int $productId
     * @param int $qty
     * @param int $summaryQty
     * @param int $origQty
     * @return \Magento\Framework\Object
     */
    public function checkQuoteItemQty($productId, $qty, $summaryQty, $origQty = 0)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->checkQuoteItemQty($qty, $summaryQty, $origQty);
    }

    /**
     * @param int $productId
     * @param int|null $qty
     * @return bool
     */
    public function verifyStock($productId, $qty = null)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->verifyStock($qty);
    }

    /**
     * @param int $productId
     * @param int|null $qty
     * @return bool
     */
    public function verifyNotification($productId, $qty = null)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->verifyNotification($qty);
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function getIsInStock($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getIsInStock();
    }

    /**
     * @param int $productId
     * @return int
     */
    public function getStockQty($productId)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->getStockQty();
    }

    /**
     * @param int $productId
     * @param int $qty
     * @return \Magento\Framework\Object
     */
    public function checkQtyIncrements($productId, $qty)
    {
        $stockItem = $this->stockItemRegistry->retrieve($productId);
        return $stockItem->checkQtyIncrements($qty);
    }

    /**
     * @param int $productTypeId
     * @return bool
     */
    public function isQty($productTypeId)
    {
        $this->getIsQtyTypeIds();
        if (!isset($this->isQtyTypeIds[$productTypeId])) {
            return false;
        }
        return $this->isQtyTypeIds[$productTypeId];
    }

    /**
     * @param int|null $filter
     * @return bool
     */
    public function getIsQtyTypeIds($filter = null)
    {
        if (null === $this->isQtyTypeIds) {
            $this->isQtyTypeIds = array();

            foreach ($this->config->getAll() as $typeId => $typeConfig) {
                $this->isQtyTypeIds[$typeId] = isset($typeConfig['is_qty']) ? $typeConfig['is_qty'] : false;
            }
        }
        if (null === $filter) {
            return $this->isQtyTypeIds;
        }
        $result = $this->isQtyTypeIds;
        foreach ($result as $key => $value) {
            if ($value !== $filter) {
                unset($result[$key]);
            }
        }
        return $result;
    }
}
