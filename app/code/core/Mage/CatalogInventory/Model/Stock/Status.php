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
 * @package     Mage_CatalogInventory
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * CatalogInventory Stock Status per website Model
 *
 * @method Mage_CatalogInventory_Model_Resource_Stock_Status _getResource()
 * @method Mage_CatalogInventory_Model_Resource_Stock_Status getResource()
 * @method int getProductId()
 * @method Mage_CatalogInventory_Model_Stock_Status setProductId(int $value)
 * @method int getWebsiteId()
 * @method Mage_CatalogInventory_Model_Stock_Status setWebsiteId(int $value)
 * @method int getStockId()
 * @method Mage_CatalogInventory_Model_Stock_Status setStockId(int $value)
 * @method float getQty()
 * @method Mage_CatalogInventory_Model_Stock_Status setQty(float $value)
 * @method int getStockStatus()
 * @method Mage_CatalogInventory_Model_Stock_Status setStockStatus(int $value)
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogInventory_Model_Stock_Status extends Mage_Core_Model_Abstract
{
    const STATUS_OUT_OF_STOCK       = 0;
    const STATUS_IN_STOCK           = 1;

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $_productTypes;

    /**
     * Websites cache
     *
     * @var array
     */
    protected $_websites;

    /**
     * Init resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_CatalogInventory_Model_Resource_Stock_Status');
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    public function getProductTypeInstances()
    {
        if (is_null($this->_productTypes)) {
            $this->_productTypes = array();
            $productEmulator     = new Varien_Object();

            foreach (array_keys(Mage_Catalog_Model_Product_Type::getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->_productTypes[$typeId] = Mage::getSingleton('Mage_Catalog_Model_Product_Type')
                    ->factory($productEmulator);
            }
        }
        return $this->_productTypes;
    }

    /**
     * Retrieve Product Type Instance By Product Type
     *
     * @param string $productType
     * @return Mage_Catalog_Model_Product_Type_Abstract
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
     * @return array
     */
    public function getWebsites($websiteId = null)
    {
        if (is_null($this->_websites)) {
            $this->_websites = $this->getResource()->getWebsiteStores();
        }

        $websites = $this->_websites;
        if (!is_null($websiteId) && isset($this->_websites[$websiteId])) {
            $websites = array($websiteId => $this->_websites[$websiteId]);
        }

        return $websites;
    }

    /**
     * Retrieve Default website store Id
     *
     * @param int $websiteId
     * @return int
     */
    public function getWebsiteDefaultStoreId($websiteId)
    {
        $websites = $this->getWebsites();
        if (isset($websites[$websiteId])) {
            return $websites[$websiteId];
        }
        return 0;
    }

    /**
     * Retrieve Catalog Product Status Model
     *
     * @return Mage_Catalog_Model_Product_Status
     */
    public function getProductStatusModel()
    {
        return Mage::getSingleton('Mage_Catalog_Model_Product_Status');
    }

    /**
     * Retrieve CatalogInventory empty Stock Item model
     *
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function getStockItemModel()
    {
        return Mage::getModel('Mage_CatalogInventory_Model_Stock_Item');
    }

    /**
     * Retrieve Product Status Enabled Constant
     *
     * @return int
     */
    public function getProductStatusEnabled()
    {
        return Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
    }

    /**
     * Change Stock Item status process
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    public function changeItemStatus(Mage_CatalogInventory_Model_Stock_Item $item)
    {
        $productId  = $item->getProductId();
        if (!$productType = $item->getProductTypeId()) {
            $productType    = $this->getProductType($productId);
        }

        $status     = (int)$item->getIsInStock();
        $qty        = (int)$item->getQty();

        $this->_processChildren($productId, $productType, $qty, $status, $item->getStockId());
        $this->_processParents($productId, $item->getStockId());

        return $this;
    }

    /**
     * Assign Stock Status to Product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $stockId
     * @param int $stockStatus
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    public function assignProduct(Mage_Catalog_Model_Product $product, $stockId = 1, $stockStatus = null)
    {
        if (is_null($stockStatus)) {
            $websiteId = $product->getStore()->getWebsiteId();
            $status = $this->getProductStatus($product->getId(), $websiteId, $stockId);
            $stockStatus = isset($status[$product->getId()]) ? $status[$product->getId()] : null;
        }

        $product->setIsSalable($stockStatus);

        return $this;
    }

    /**
     * Rebuild stock status for all products
     *
     * @param int $websiteId
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    public function rebuild($websiteId = null)
    {
        $lastProductId = 0;
        while (true) {
            $productCollection = $this->getResource()->getProductCollection($lastProductId);
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
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    public function updateStatus($productId, $productType = null, $websiteId = null)
    {
        if (is_null($productType)) {
            $productType = $this->getProductType($productId);
        }

        $item = $this->getStockItemModel()->loadByProduct($productId);

        $status  = self::STATUS_IN_STOCK;
        $qty     = 0;
        if ($item->getId()) {
            $status = $item->getIsInStock();
            $qty    = $item->getQty();
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
     * @param float $qty
     * @param int $status
     * @param int $stockId
     * @param int $websiteId
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    protected function _processChildren($productId, $productType, $qty = 0, $status = self::STATUS_IN_STOCK, $stockId = 1, $websiteId = null)
    {
        if ($status == self::STATUS_OUT_OF_STOCK) {
            $this->saveProductStatus($productId, $status, $qty, $stockId, $websiteId);
            return $this;
        }

        $statuses   = array();
        $websites   = $this->getWebsites($websiteId);

        foreach (array_keys($websites) as $websiteId) {
            /* @var $website Mage_Core_Model_Website */
            $statuses[$websiteId] = $status;
        }

        if (!$typeInstance = $this->getProductTypeInstance($productType)) {
            return $this;
        }

        $requiredChildrenIds = $typeInstance->getChildrenIds($productId, true);
        if ($requiredChildrenIds) {
            $childrenIds = array();
            foreach ($requiredChildrenIds as $groupedChildrenIds) {
                $childrenIds = array_merge($childrenIds, $groupedChildrenIds);
            }
            $childrenWebsites = Mage::getSingleton('Mage_Catalog_Model_Product_Website')
                ->getWebsites($childrenIds);
            foreach ($websites as $websiteId => $storeId) {
                $childrenStatus = $this->getProductStatusModel()
                    ->getProductStatus($childrenIds, $storeId);
                $childrenStock  = $this->getProductStatus($childrenIds, $websiteId, $stockId);

                $websiteStatus = $statuses[$websiteId];
                foreach ($requiredChildrenIds as $groupedChildrenIds) {
                    $optionStatus = false;
                    foreach ($groupedChildrenIds as $childId) {
                        if (isset($childrenStatus[$childId])
                            and isset($childrenWebsites[$childId])
                            and in_array($websiteId, $childrenWebsites[$childId])
                            and $childrenStatus[$childId] == $this->getProductStatusEnabled()
                            and isset($childrenStock[$childId])
                            and $childrenStock[$childId] == self::STATUS_IN_STOCK
                        ) {
                            $optionStatus = true;
                        }
                    }
                    $websiteStatus = $websiteStatus && $optionStatus;
                }
                $statuses[$websiteId] = (int)$websiteStatus;
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
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    protected function _processParents($productId, $stockId = 1, $websiteId = null)
    {
        $parentIds = array();
        foreach ($this->getProductTypeInstances() as $typeInstance) {
            /* @var $typeInstance Mage_Catalog_Model_Product_Type_Abstract */
            $parentIds = array_merge($parentIds, $typeInstance->getParentIdsByChild($productId));
        }

        if (!$parentIds) {
            return $this;
        }

        $productTypes = $this->getProductsType($parentIds);
        $item         = $this->getStockItemModel();

        foreach ($parentIds as $parentId) {
            $parentType = isset($productTypes[$parentId]) ? $productTypes[$parentId] : null;
            $item->setData(array('stock_id' => $stockId))
                ->setOrigData()
                ->loadByProduct($parentId);
            $status  = self::STATUS_IN_STOCK;
            $qty     = 0;
            if ($item->getId()) {
                $status = $item->getIsInStock();
                $qty    = $item->getQty();
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
     * @param float $qty
     * @param int $stockId
     * @param int|null $websiteId
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    public function saveProductStatus($productId, $status, $qty = 0, $stockId = 1, $websiteId = null)
    {
        $this->getResource()->saveProductStatus($this, $productId, $status, $qty, $stockId, $websiteId);
        return $this;
    }

    /**
     * Retrieve Product(s) status
     *
     * @param int|array $productIds
     * @param int $websiteId
     * @param int $stockId
     * @return array
     */
    public function getProductStatus($productIds, $websiteId, $stockId = 1)
    {
        return $this->getResource()->getProductStatus($productIds, $websiteId, $stockId);
    }

    /**
     * Retrieve Product(s) Data array
     *
     * @param int|array $productIds
     * @param int $websiteId
     * @param int $stockId
     * @return array
     */
    public function getProductData($productIds, $websiteId, $stockId = 1)
    {
        return $this->getResource()->getProductData($productIds, $websiteId, $stockId);
    }

    /**
     * Retrieve Product Type
     *
     * @param int $productId
     * @return string|false
     */
    public function getProductType($productId)
    {
        $types = $this->getResource()->getProductsType($productId);
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
        return $this->getResource()->getProductsType($productIds);
    }

    /**
     * Add information about stock status to product collection
     *
     * @param   Mage_Catalog_Model_Resource_Product_Collection $productCollection
     * @param   int|null $websiteId
     * @param   int|null $stockId
     * @return  Mage_CatalogInventory_Model_Stock_Status
     */
    public function addStockStatusToProducts($productCollection, $websiteId = null, $stockId = null)
    {
        if ($stockId === null) {
            $stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
        }
        if ($websiteId === null) {
            $websiteId = Mage::app()->getStore()->getWebsiteId();
            if ((int)$websiteId == 0 && $productCollection->getStoreId()) {
                $websiteId = Mage::app()->getStore($productCollection->getStoreId())->getWebsiteId();
            }
        }
        $productIds = array();
        foreach ($productCollection as $product) {
            $productIds[] = $product->getId();
        }

        if (!empty($productIds)) {
            $stockStatuses = $this->_getResource()->getProductStatus($productIds, $websiteId, $stockId);
            foreach ($stockStatuses as $productId => $status) {
                if ($product = $productCollection->getItemById($productId)) {
                    $product->setIsSalable($status);
                }
            }
        }

        /* back compatible stock item */
        foreach ($productCollection as $product) {
            $object = new Varien_Object(array('is_in_stock' => $product->getData('is_salable')));
            $product->setStockItem($object);
        }

        return $this;
    }

    /**
     * Add stock status to prepare index select
     *
     * @param Varien_Db_Select $select
     * @param Mage_Core_Model_Website $website
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    public function addStockStatusToSelect(Varien_Db_Select $select, Mage_Core_Model_Website $website)
    {
        $this->_getResource()->addStockStatusToSelect($select, $website);
        return $this;
    }

    /**
     * Add stock status limitation to catalog product price index select object
     *
     * @param Varien_Db_Select $select
     * @param string|Zend_Db_Expr $entityField
     * @param string|Zend_Db_Expr $websiteField
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    public function prepareCatalogProductIndexSelect(Varien_Db_Select $select, $entityField, $websiteField)
    {
        if (Mage::helper('Mage_CatalogInventory_Helper_Data')->isShowOutOfStock()) {
            return $this;
        }

        $this->_getResource()->prepareCatalogProductIndexSelect($select, $entityField, $websiteField);

        return $this;
    }

    /**
     * Add only is in stock products filter to product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $this->_getResource()->addIsInStockFilterToCollection($collection);
        return $this;
    }
}
