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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * CatalogInventory Stock Status per website Resource Model
 */
namespace Magento\CatalogInventory\Model\Resource\Stock;

class Status extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Store model manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Website model factory
     *
     * @var \Magento\Core\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\WebsiteFactory $websiteFactory
     */
    public function __construct(
        \Magento\Core\Model\Resource $resource,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\WebsiteFactory $websiteFactory
    ) {
        parent::__construct($resource);

        $this->_storeManager = $storeManager;
        $this->_websiteFactory = $websiteFactory;
    }

    /**
     * Resource model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('cataloginventory_stock_status', 'product_id');
    }

    /**
     * Save Product Status per website
     *
     * @param \Magento\CatalogInventory\Model\Stock\Status $object
     * @param int $productId
     * @param int $status
     * @param float $qty
     * @param int $stockId
     * @param int|null $websiteId
     * @return \Magento\CatalogInventory\Model\Resource\Stock\Status
     */
    public function saveProductStatus(\Magento\CatalogInventory\Model\Stock\Status $object, $productId, $status, $qty = 0,
        $stockId = 1, $websiteId = null)
    {
        $websites = array_keys($object->getWebsites($websiteId));
        $adapter = $this->_getWriteAdapter();
        foreach ($websites as $websiteId) {
            $select = $adapter->select()
                ->from($this->getMainTable())
                ->where('product_id = :product_id')
                ->where('website_id = :website_id')
                ->where('stock_id = :stock_id');
            $bind = array(
                ':product_id' => $productId,
                ':website_id' => $websiteId,
                ':stock_id'   => $stockId
            );
            if ($row = $adapter->fetchRow($select, $bind)) {
                $bind = array(
                    'qty'           => $qty,
                    'stock_status'  => $status
                );
                $where = array(
                    $adapter->quoteInto('product_id=?', (int)$row['product_id']),
                    $adapter->quoteInto('website_id=?', (int)$row['website_id']),
                    $adapter->quoteInto('stock_id=?', (int)$row['stock_id']),
                );
                $adapter->update($this->getMainTable(), $bind, $where);
            } else {
                $bind = array(
                    'product_id'    => $productId,
                    'website_id'    => $websiteId,
                    'stock_id'      => $stockId,
                    'qty'           => $qty,
                    'stock_status'  => $status
                );
                $adapter->insert($this->getMainTable(), $bind);
            }
        }

        return $this;
    }

    /**
     * Retrieve product status
     * Return array as key product id, value - stock status
     *
     * @param int|array $productIds
     * @param int $websiteId
     * @param int $stockId
     * @return array
     */
    public function getProductStatus($productIds, $websiteId, $stockId = 1)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('product_id', 'stock_status'))
            ->where('product_id IN(?)', $productIds)
            ->where('stock_id=?', (int)$stockId)
            ->where('website_id=?', (int)$websiteId);
        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Retrieve product(s) data array
     *
     * @param int|array $productIds
     * @param int $websiteId
     * @param int $stockId
     * @return array
     */
    public function getProductData($productIds, $websiteId, $stockId = 1)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $result = array();

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('product_id IN(?)', $productIds)
            ->where('stock_id=?', (int)$stockId)
            ->where('website_id=?', (int)$websiteId);
        $result = $this->_getReadAdapter()->fetchAssoc($select);
        return $result;
    }

    /**
     * Retrieve websites and default stores
     * Return array as key website_id, value store_id
     *
     * @return array
     */
    public function getWebsiteStores()
    {
        /** @var \Magento\Core\Model\Website $website */
        $website = $this->_websiteFactory->create();
        return $this->_getReadAdapter()->fetchPairs($website->getDefaultStoresSelect(false));
    }

    /**
     * Retrieve Product Type
     *
     * @param array|int $productIds
     * @return array
     */
    public function getProductsType($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $select = $this->_getReadAdapter()->select()
            ->from(
                array('e' => $this->getTable('catalog_product_entity')),
                array('entity_id', 'type_id')
            )
            ->where('entity_id IN(?)', $productIds);
        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Retrieve Product part Collection array
     * Return array as key product id, value product type
     *
     * @param int $lastEntityId
     * @param int $limit
     * @return array
     */
    public function getProductCollection($lastEntityId = 0, $limit = 1000)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('e' => $this->getTable('catalog_product_entity')),
                array('entity_id', 'type_id')
            )
            ->order('entity_id ASC')
            ->where('entity_id > :entity_id')
            ->limit($limit);
        return $this->_getReadAdapter()->fetchPairs($select, array(':entity_id' => $lastEntityId));
    }

    /**
     * Add stock status to prepare index select
     *
     * @param \Magento\DB\Select $select
     * @param \Magento\Core\Model\Website $website
     * @return \Magento\CatalogInventory\Model\Resource\Stock\Status
     */
    public function addStockStatusToSelect(\Magento\DB\Select $select, \Magento\Core\Model\Website $website)
    {
        $websiteId = $website->getId();
        $select->joinLeft(
            array('stock_status' => $this->getMainTable()),
            'e.entity_id = stock_status.product_id AND stock_status.website_id='.$websiteId,
            array('salable' => 'stock_status.stock_status')
        );

        return $this;
    }

    /**
     * Add stock status limitation to catalog product price index select object
     *
     * @param \Magento\DB\Select $select
     * @param string|Zend_Db_Expr $entityField
     * @param string|Zend_Db_Expr $websiteField
     * @return \Magento\CatalogInventory\Model\Resource\Stock\Status
     */
    public function prepareCatalogProductIndexSelect(\Magento\DB\Select $select, $entityField, $websiteField)
    {
        $select->join(
            array('ciss' => $this->getMainTable()),
            "ciss.product_id = {$entityField} AND ciss.website_id = {$websiteField}",
            array()
        );
        $select->where('ciss.stock_status = ?', \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK);

        return $this;
    }

    /**
     * Add only is in stock products filter to product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @return \Magento\CatalogInventory\Model\Resource\Stock\Status
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $websiteId = $this->_storeManager->getStore($collection->getStoreId())->getWebsiteId();
        $joinCondition = $this->_getReadAdapter()
            ->quoteInto('e.entity_id = stock_status_index.product_id'
                . ' AND stock_status_index.website_id = ?', $websiteId
            );

        $joinCondition .= $this->_getReadAdapter()->quoteInto(
            ' AND stock_status_index.stock_id = ?',
            \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID
        );

        $collection->getSelect()
            ->join(
                array('stock_status_index' => $this->getMainTable()),
                $joinCondition,
                array()
            )
            ->where('stock_status_index.stock_status=?', \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK);

        return $this;
    }
}
