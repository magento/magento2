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
 * @package     Mage_Wishlist
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist item collection
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_Model_Resource_Item_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Product Visibility Filter to product collection flag
     *
     * @var bool
     */
    protected $_productVisible = false;

    /**
     * Product Salable Filter to product collection flag
     *
     * @var bool
     */
    protected $_productSalable = false;

    /**
     * If product out of stock, its item will be removed after load
     *
     * @var bool
     */
    protected $_productInStock = false;

    /**
     * Product Ids array
     *
     * @var array
     */
    protected $_productIds = array();

    /**
     * Store Ids array
     *
     * @var array
     */
    protected $_storeIds = array();

    /**
     * Add days in whishlist filter of product collection
     *
     * @var boolean
     */
    protected $_addDaysInWishlist = false;

    /**
     * Sum of items collection qty
     *
     * @var int
     */
    protected $_itemsQty;

    /**
     * Whether product name attribute value table is joined in select
     *
     * @var boolean
     */
    protected $_isProductNameJoined = false;

    /**
     * Initialize resource model for collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Mage_Wishlist_Model_Item', 'Mage_Wishlist_Model_Resource_Item');
        $this->addFilterToMap('store_id', 'main_table.store_id');
    }

    /**
     * After load processing
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        /**
         * Assign products
         */
        $this->_assignOptions();
        $this->_assignProducts();
        $this->resetItemsDataChanged();

        $this->getPageSize();

        return $this;
    }

    /**
     * Add options to items
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _assignOptions()
    {
        $itemIds = array_keys($this->_items);
        /* @var $optionCollection Mage_Wishlist_Model_Resource_Item_Option_Collection */
        $optionCollection = Mage::getModel('Mage_Wishlist_Model_Item_Option')->getCollection();
        $optionCollection->addItemFilter($itemIds);

        /* @var $item Mage_Wishlist_Model_Item */
        foreach ($this as $item) {
            $item->setOptions($optionCollection->getOptionsByItem($item));
        }
        $productIds = $optionCollection->getProductIds();
        $this->_productIds = array_merge($this->_productIds, $productIds);

        return $this;
    }

    /**
     * Add products to items and item options
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _assignProducts()
    {
        Magento_Profiler::start('WISHLIST:'.__METHOD__);
        $productIds = array();

        $isStoreAdmin = Mage::app()->getStore()->isAdmin();

        $storeIds = array();
        foreach ($this as $item) {
            $productIds[$item->getProductId()] = 1;
            if ($isStoreAdmin && !in_array($item->getStoreId(), $storeIds)) {
                $storeIds[] = $item->getStoreId();
            }
        }
        if (!$isStoreAdmin) {
            $storeIds = $this->_storeIds;
        }

        $this->_productIds = array_merge($this->_productIds, array_keys($productIds));
        $attributes = Mage::getSingleton('Mage_Wishlist_Model_Config')->getProductAttributes();
        $productCollection = Mage::getModel('Mage_Catalog_Model_Product')->getCollection();
        foreach ($storeIds as $id) {
            $productCollection->addStoreFilter($id);
        }
        $productCollection->addPriceData()
            ->addTaxPercents()
            ->addIdFilter($this->_productIds)
            ->addAttributeToSelect($attributes)
            ->addOptionsToResult()
            ->addUrlRewrite();

        if ($this->_productVisible) {
            $productCollection->setVisibility(
                Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInSiteIds()
            );
        }
        if ($this->_productSalable) {
            $productCollection = Mage::helper('Mage_Adminhtml_Helper_Sales')->applySalableProductTypesFilter($productCollection);
        }

        Mage::dispatchEvent('wishlist_item_collection_products_after_load', array(
            'product_collection' => $productCollection
        ));

        $checkInStock = $this->_productInStock && !Mage::helper('Mage_CatalogInventory_Helper_Data')->isShowOutOfStock();

        foreach ($this as $item) {
            $product = $productCollection->getItemById($item->getProductId());
            if ($product) {
                if ($checkInStock && !$product->isSalable()) {
                    $this->removeItemByKey($item->getId());
                } else {
                    $product->setCustomOptions(array());
                    $item->setProduct($product);
                    $item->setProductName($product->getName());
                    $item->setName($product->getName());
                    $item->setPrice($product->getPrice());
                }
            } else {
                $item->isDeleted(true);
            }
        }

        Magento_Profiler::stop('WISHLIST:'.__METHOD__);

        return $this;
    }

    /**
     * Add filter by wishlist object
     *
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addWishlistFilter(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        $this->addFieldToFilter('wishlist_id', $wishlist->getId());
        return $this;
    }

    /**
     * Add filter by shared stores
     *
     * @param array $storeIds
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addStoreFilter($storeIds = array())
    {
        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }
        $this->_storeIds = $storeIds;
        $this->addFieldToFilter('store_id', array('in' => $this->_storeIds));

        return $this;
    }

    /**
     * Add items store data to collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addStoreData()
    {
        $storeTable = Mage::getSingleton('Mage_Core_Model_Resource')->getTableName('core_store');
        $this->getSelect()->join(array('store'=>$storeTable), 'main_table.store_id=store.store_id', array(
            'store_name'=>'name',
            'item_store_id' => 'store_id'
        ));
        return $this;
    }

    /**
     * Reset sort order
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function resetSortOrder()
    {
        $this->getSelect()->reset(Zend_Db_Select::ORDER);
        return $this;
    }

    /**
     * Set product Visibility Filter to product collection flag
     *
     * @param bool $flag
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function setVisibilityFilter($flag = true)
    {
        $this->_productVisible = (bool)$flag;
        return $this;
    }

    /**
     * Set Salable Filter.
     * This filter apply Salable Product Types Filter to product collection.
     *
     * @param bool $flag
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function setSalableFilter($flag = true)
    {
        $this->_productSalable = (bool)$flag;
        return $this;
    }

    /**
     * Set In Stock Filter.
     * This filter remove items with no salable product.
     *
     * @param bool $flag
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function setInStockFilter($flag = true)
    {
        $this->_productInStock = (bool)$flag;
        return $this;
    }

    /**
     * Set add days in wishlist
     *
     * @TODO This method should be refactored. It appears in 1.5.0.0 in deprecated state, because:
     * @TODO - we need it to make wishlist item collection interface as much as possible compatible with old
     * @TODO  wishlist product collection
     * @TODO - this method is useless because we can calculate days in php, and don't use MySQL for it
     *
     * @deprecated since 1.5.0.0
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addDaysInWishlist()
    {
        $this->_addDaysInWishlist = true;

        $adapter = $this->getConnection();
        $dateModel = Mage::getSingleton('Mage_Core_Model_Date');
        $resHelper = Mage::getResourceHelper('Mage_Core');

        $offsetFromDb = (int) $dateModel->getGmtOffset();
        $startDate = $adapter->getDateAddSql('added_at', $offsetFromDb, Varien_Db_Adapter_Interface::INTERVAL_SECOND);

        $nowDate = $dateModel->date();
        $dateDiff = $resHelper->getDateDiff($startDate, $adapter->formatDate($nowDate));

        $this->getSelect()->columns(array('days_in_wishlist' => $dateDiff));
        return $this;
    }

    /**
     * Adds filter on days in wishlist
     *
     * $constraints may contain 'from' and 'to' indexes with number of days to look for items
     *
     * @param array $constraints
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addDaysFilter($constraints)
    {
        if (!is_array($constraints)) {
            return $this;
        }

        $filter = array();

        $now = Mage::getSingleton('Mage_Core_Model_Date')->date();
        $gmtOffset = (int) Mage::getSingleton('Mage_Core_Model_Date')->getGmtOffset();
        if (isset($constraints['from'])) {
            $lastDay = new Zend_Date($now, Varien_Date::DATETIME_INTERNAL_FORMAT);
            $lastDay->subSecond($gmtOffset)
                ->subDay($constraints['from'] - 1);
            $filter['to'] = $lastDay;
        }

        if (isset($constraints['to'])) {
            $firstDay = new Zend_Date($now, Varien_Date::DATETIME_INTERNAL_FORMAT);
            $firstDay->subSecond($gmtOffset)
                ->subDay($constraints['to']);
            $filter['from'] = $firstDay;
        }

        if ($filter) {
            $filter['datetime'] = true;
            $this->addFieldToFilter('added_at', $filter);
        }

        return $this;
    }

    /**
     * Joins product name attribute value to use it in WHERE and ORDER clauses
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _joinProductNameTable()
    {
        if (!$this->_isProductNameJoined) {
            $entityTypeId = Mage::getResourceModel('Mage_Catalog_Model_Resource_Config')
                    ->getEntityTypeId();
            $attribute = Mage::getModel('Mage_Catalog_Model_Entity_Attribute')
                ->loadByCode($entityTypeId, 'name');

            $storeId = Mage::app()->getStore()->getId();

            $this->getSelect()
                ->join(
                    array('product_name_table' => $attribute->getBackendTable()),
                    'product_name_table.entity_id=main_table.product_id' .
                        ' AND product_name_table.store_id=' . $storeId .
                        ' AND product_name_table.attribute_id=' . $attribute->getId().
                        ' AND product_name_table.entity_type_id=' . $entityTypeId,
                    array()
                );

            $this->_isProductNameJoined = true;
        }
        return $this;
    }

    /**
     * Adds filter on product name
     *
     * @param string $productName
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addProductNameFilter($productName)
    {
        $this->_joinProductNameTable();
        $this->getSelect()
            ->where('INSTR(product_name_table.value, ?)', $productName);

        return $this;
    }

    /**
     * Sets ordering by product name
     *
     * @param string $dir
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function setOrderByProductName($dir)
    {
        $this->_joinProductNameTable();
        $this->getSelect()->order('product_name_table.value ' . $dir);
        return $this;
    }

    /**
     * Get sum of items collection qty
     *
     * @return int
     */
    public function getItemsQty(){
        if (is_null($this->_itemsQty)) {
            $this->_itemsQty = 0;
            foreach ($this as $wishlistItem) {
                $qty = $wishlistItem->getQty();
                $this->_itemsQty += ($qty === 0) ? 1 : $qty;
            }
        }

        return (int)$this->_itemsQty;
    }
}
