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
 * @package     Magento_Reports
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product Low Stock Report Collection
 *
 * @category    Magento
 * @package     Magento_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Product\Lowstock;

class Collection extends \Magento\Reports\Model\Resource\Product\Collection
{
    /**
     * Flag about is joined CatalogInventory Stock Item
     *
     * @var bool
     */
    protected $_inventoryItemJoined        = false;

    /**
     * Alias for CatalogInventory Stock Item Table
     *
     * @var string
     */
    protected $_inventoryItemTableAlias    = 'lowstock_inventory_item';

    /**
     * Catalog inventory data
     *
     * @var \Magento\CatalogInventory\Helper\Data
     */
    protected $_inventoryData = null;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\Item
     */
    protected $_itemResource;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\App\Resource $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Validator\UniversalFactory $universalFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Resource\Product $product
     * @param \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\CatalogInventory\Helper\Data $catalogInventoryData
     * @param \Magento\CatalogInventory\Model\Resource\Stock\Item $itemResource
     * @param mixed $connection
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\App\Resource $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Validator\UniversalFactory $universalFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Catalog\Model\Resource\Product $product,
        \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\CatalogInventory\Helper\Data $catalogInventoryData,
        \Magento\CatalogInventory\Model\Resource\Stock\Item $itemResource,
        $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $catalogData,
            $catalogProductFlatState,
            $coreStoreConfig,
            $productOptionFactory,
            $catalogUrl,
            $locale,
            $customerSession,
            $dateTime,
            $product,
            $eventTypeFactory,
            $productType,
            $connection
        );
        $this->_inventoryData = $catalogInventoryData;
        $this->_itemResource = $itemResource;
    }

    /**
     * Retrieve CatalogInventory Stock Item Table
     *
     * @return string
     */
    protected function _getInventoryItemTable()
    {
        return $this->_itemResource->getMainTable();
    }

    /**
     * Retrieve CatalogInventory Stock Item Table Id field name
     *
     * @return string
     */
    protected function _getInventoryItemIdField()
    {
        return $this->_itemResource->getIdFieldName();
    }

    /**
     * Retrieve alias for CatalogInventory Stock Item Table
     *
     * @return string
     */
    protected function _getInventoryItemTableAlias()
    {
        return $this->_inventoryItemTableAlias;
    }

    /**
     * Add catalog inventory stock item field to select
     *
     * @param string $field
     * @param string $alias
     * @return $this
     */
    protected function _addInventoryItemFieldToSelect($field, $alias = null)
    {
        if (empty($alias)) {
            $alias = $field;
        }

        if (isset($this->_joinFields[$alias])) {
            return $this;
        }

        $this->_joinFields[$alias] = array(
            'table' => $this->_getInventoryItemTableAlias(),
            'field' => $field
        );

        $this->getSelect()->columns(array($alias => $field), $this->_getInventoryItemTableAlias());
        return $this;
    }

    /**
     * Retrieve catalog inventory stock item field correlation name
     *
     * @param string $field
     * @return string
     */
    protected function _getInventoryItemField($field)
    {
        return sprintf('%s.%s', $this->_getInventoryItemTableAlias(), $field);
    }

    /**
     * Join catalog inventory stock item table for further stock_item values filters
     *
     * @param array $fields
     * @return $this
     */
    public function joinInventoryItem($fields = array())
    {
        if (!$this->_inventoryItemJoined) {
            $this->getSelect()->join(
                array($this->_getInventoryItemTableAlias() => $this->_getInventoryItemTable()),
                sprintf('e.%s = %s.product_id',
                    $this->getEntity()->getEntityIdField(),
                    $this->_getInventoryItemTableAlias()
                ),
                array()
            );
            $this->_inventoryItemJoined = true;
        }

        if (!is_array($fields)) {
            if (empty($fields)) {
                $fields = array();
            } else {
                $fields = array($fields);
            }
        }

        foreach ($fields as $alias => $field) {
            if (!is_string($alias)) {
                $alias = null;
            }
            $this->_addInventoryItemFieldToSelect($field, $alias);
        }

        return $this;
    }

    /**
     * Add filter by product type(s)
     *
     * @param array|string $typeFilter
     * @return $this
     */
    public function filterByProductType($typeFilter)
    {
        if (!is_string($typeFilter) && !is_array($typeFilter)) {
            new \Magento\Core\Exception(__('The product type filter specified is incorrect.'));
        }
        $this->addAttributeToFilter('type_id', $typeFilter);
        return $this;
    }

    /**
     * Add filter by product types from config - only types which have QTY parameter
     *
     * @return $this
     */
    public function filterByIsQtyProductTypes()
    {
        $this->filterByProductType(
            array_keys(array_filter($this->_inventoryData->getIsQtyTypeIds()))
        );
        return $this;
    }

    /**
     * Add Use Manage Stock Condition to collection
     *
     * @param null|int $storeId
     * @return $this
     */
    public function useManageStockFilter($storeId = null)
    {
        $this->joinInventoryItem();
        $manageStockExpr = $this->getConnection()->getCheckSql(
            $this->_getInventoryItemField('use_config_manage_stock') . ' = 1',
            (int) $this->_coreStoreConfig->getConfig(\Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MANAGE_STOCK, $storeId),
            $this->_getInventoryItemField('manage_stock')
        );
        $this->getSelect()->where($manageStockExpr . ' = ?', 1);
        return $this;
    }

    /**
     * Add Notify Stock Qty Condition to collection
     *
     * @param null|int $storeId
     * @return $this
     */
    public function useNotifyStockQtyFilter($storeId = null)
    {
        $this->joinInventoryItem(array('qty'));
        $notifyStockExpr = $this->getConnection()->getCheckSql(
            $this->_getInventoryItemField('use_config_notify_stock_qty') . ' = 1',
            (int)$this->_coreStoreConfig->getConfig(\Magento\CatalogInventory\Model\Stock\Item::XML_PATH_NOTIFY_STOCK_QTY, $storeId),
            $this->_getInventoryItemField('notify_stock_qty')
        );
        $this->getSelect()->where('qty < ?', $notifyStockExpr);
        return $this;
    }
}
