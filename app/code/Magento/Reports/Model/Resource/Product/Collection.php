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


/**
 * Products Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Product;

class Collection extends \Magento\Catalog\Model\Resource\Product\Collection
{
    const SELECT_COUNT_SQL_TYPE_CART = 1;

    /**
     * Product entity identifier
     *
     * @var int
     */
    protected $_productEntityId;

    /**
     * Product entity table name
     *
     * @var string
     */
    protected $_productEntityTableName;

    /**
     * Product entity type identifier
     *
     * @var int
     */
    protected $_productEntityTypeId;

    /**
     * Select count
     *
     * @var int
     */
    protected $_selectCountSqlType = 0;

    /**
     * @var \Magento\Reports\Model\Event\TypeFactory
     */
    protected $_eventTypeFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Resource\Product $product
     * @param \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param mixed $connection
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Catalog\Model\Resource\Product $product,
        \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory,
        \Magento\Catalog\Model\Product\Type $productType,
        $connection = null
    ) {
        $this->setProductEntityId($product->getEntityIdField());
        $this->setProductEntityTableName($product->getEntityTable());
        $this->setProductEntityTypeId($product->getTypeId());
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
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $connection
        );
        $this->_eventTypeFactory = $eventTypeFactory;
        $this->_productType = $productType;
    }

    /**
     * Set Type for COUNT SQL Select
     *
     * @param int $type
     * @return $this
     */
    public function setSelectCountSqlType($type)
    {
        $this->_selectCountSqlType = $type;
        return $this;
    }

    /**
     * Set product entity id
     *
     * @param string $entityId
     * @return $this
     */
    public function setProductEntityId($entityId)
    {
        $this->_productEntityId = (int)$entityId;
        return $this;
    }

    /**
     * Get product entity id
     *
     * @return int
     */
    public function getProductEntityId()
    {
        return $this->_productEntityId;
    }

    /**
     * Set product entity table name
     *
     * @param string $value
     * @return $this
     */
    public function setProductEntityTableName($value)
    {
        $this->_productEntityTableName = $value;
        return $this;
    }

    /**
     * Get product entity table name
     *
     * @return string
     */
    public function getProductEntityTableName()
    {
        return $this->_productEntityTableName;
    }

    /**
     * Set product entity type id
     *
     * @param int $value
     * @return $this
     */
    public function setProductEntityTypeId($value)
    {
        $this->_productEntityTypeId = $value;
        return $this;
    }

    /**
     * Get product entity type id
     *
     * @return int
     */
    public function getProductEntityTypeId()
    {
        return $this->_productEntityTypeId;
    }

    /**
     * Join fields
     *
     * @return $this
     */
    protected function _joinFields()
    {
        $this->_totals = new \Magento\Framework\Object();

        $this->addAttributeToSelect('entity_id')->addAttributeToSelect('name')->addAttributeToSelect('price');

        return $this;
    }

    /**
     * Get select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        if ($this->_selectCountSqlType == self::SELECT_COUNT_SQL_TYPE_CART) {
            $countSelect = clone $this->getSelect();
            $countSelect->reset()->from(
                array('quote_item_table' => $this->getTable('sales_flat_quote_item')),
                array('COUNT(DISTINCT quote_item_table.product_id)')
            )->join(
                array('quote_table' => $this->getTable('sales_flat_quote')),
                'quote_table.entity_id = quote_item_table.quote_id AND quote_table.is_active = 1',
                array()
            );
            return $countSelect;
        }

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);
        $countSelect->reset(\Zend_Db_Select::GROUP);
        $countSelect->reset(\Zend_Db_Select::HAVING);
        $countSelect->columns("count(DISTINCT e.entity_id)");

        return $countSelect;
    }

    /**
     * Add carts count
     *
     * @return $this
     */
    public function addCartsCount()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset();

        $countSelect->from(
            array('quote_items' => $this->getTable('sales_flat_quote_item')),
            'COUNT(*)'
        )->join(
            array('quotes' => $this->getTable('sales_flat_quote')),
            'quotes.entity_id = quote_items.quote_id AND quotes.is_active = 1',
            array()
        )->where(
            "quote_items.product_id = e.entity_id"
        );

        $this->getSelect()->columns(
            array("carts" => "({$countSelect})")
        )->group(
            "e.{$this->getProductEntityId()}"
        )->having(
            'carts > ?',
            0
        );

        return $this;
    }

    /**
     * Add orders count
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function addOrdersCount($from = '', $to = '')
    {
        $orderItemTableName = $this->getTable('sales_flat_order_item');
        $productFieldName = sprintf('e.%s', $this->getProductEntityId());

        $this->getSelect()->joinLeft(
            array('order_items' => $orderItemTableName),
            "order_items.product_id = {$productFieldName}",
            array()
        )->columns(
            array('orders' => 'COUNT(order_items2.item_id)')
        )->group(
            $productFieldName
        );

        $dateFilter = array('order_items2.item_id = order_items.item_id');
        if ($from != '' && $to != '') {
            $dateFilter[] = $this->_prepareBetweenSql('order_items2.created_at', $from, $to);
        }

        $this->getSelect()->joinLeft(
            array('order_items2' => $orderItemTableName),
            implode(' AND ', $dateFilter),
            array()
        );

        return $this;
    }

    /**
     * Add ordered qty's
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function addOrderedQty($from = '', $to = '')
    {
        $adapter = $this->getConnection();
        $compositeTypeIds = $this->_productType->getCompositeTypes();
        $orderTableAliasName = $adapter->quoteIdentifier('order');

        $orderJoinCondition = array(
            $orderTableAliasName . '.entity_id = order_items.order_id',
            $adapter->quoteInto("{$orderTableAliasName}.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED)
        );

        $productJoinCondition = array(
            $adapter->quoteInto('(e.type_id NOT IN (?))', $compositeTypeIds),
            'e.entity_id = order_items.product_id',
            $adapter->quoteInto('e.entity_type_id = ?', $this->getProductEntityTypeId())
        );

        if ($from != '' && $to != '') {
            $fieldName = $orderTableAliasName . '.created_at';
            $orderJoinCondition[] = $this->_prepareBetweenSql($fieldName, $from, $to);
        }

        $this->getSelect()->reset()->from(
            array('order_items' => $this->getTable('sales_flat_order_item')),
            array('ordered_qty' => 'SUM(order_items.qty_ordered)', 'order_items_name' => 'order_items.name')
        )->joinInner(
            array('order' => $this->getTable('sales_flat_order')),
            implode(' AND ', $orderJoinCondition),
            array()
        )->joinLeft(
            array('e' => $this->getProductEntityTableName()),
            implode(' AND ', $productJoinCondition),
            array(
                'entity_id' => 'order_items.product_id',
                'entity_type_id' => 'e.entity_type_id',
                'attribute_set_id' => 'e.attribute_set_id',
                'type_id' => 'e.type_id',
                'sku' => 'e.sku',
                'has_options' => 'e.has_options',
                'required_options' => 'e.required_options',
                'created_at' => 'e.created_at',
                'updated_at' => 'e.updated_at'
            )
        )->where(
            'parent_item_id IS NULL'
        )->group(
            'order_items.product_id'
        )->having(
            'SUM(order_items.qty_ordered) > ?',
            0
        );
        return $this;
    }

    /**
     * Set order
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        if (in_array($attribute, array('carts', 'orders', 'ordered_qty'))) {
            $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * Add views count
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function addViewsCount($from = '', $to = '')
    {
        /**
         * Getting event type id for catalog_product_view event
         */
        $eventTypes = $this->_eventTypeFactory->create()->getCollection();
        foreach ($eventTypes as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = (int)$eventType->getId();
                break;
            }
        }

        $this->getSelect()->reset()->from(
            array('report_table_views' => $this->getTable('report_event')),
            array('views' => 'COUNT(report_table_views.event_id)')
        )->join(
            array('e' => $this->getProductEntityTableName()),
            $this->getConnection()->quoteInto(
                "e.entity_id = report_table_views.object_id AND e.entity_type_id = ?",
                $this->getProductEntityTypeId()
            )
        )->where(
            'report_table_views.event_type_id = ?',
            $productViewEvent
        )->group(
            'e.entity_id'
        )->order(
            'views ' . self::SORT_ORDER_DESC
        )->having(
            'COUNT(report_table_views.event_id) > ?',
            0
        );

        if ($from != '' && $to != '') {
            $this->getSelect()->where('logged_at >= ?', $from)->where('logged_at <= ?', $to);
        }
        return $this;
    }

    /**
     * Prepare between sql
     *
     * @param string $fieldName Field name with table suffix ('created_at' or 'main_table.created_at')
     * @param string $from
     * @param string $to
     * @return string Formatted sql string
     */
    protected function _prepareBetweenSql($fieldName, $from, $to)
    {
        return sprintf(
            '(%s BETWEEN %s AND %s)',
            $fieldName,
            $this->getConnection()->quote($from),
            $this->getConnection()->quote($to)
        );
    }

    /**
     * Add store restrictions to product collection
     *
     * @param array $storeIds
     * @param array $websiteIds
     * @return $this
     */
    public function addStoreRestrictions($storeIds, $websiteIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }
        if (!is_array($websiteIds)) {
            $websiteIds = array($websiteIds);
        }

        $filters = $this->_productLimitationFilters;
        if (isset($filters['store_id'])) {
            if (!in_array($filters['store_id'], $storeIds)) {
                $this->addStoreFilter($filters['store_id']);
            } else {
                $this->addStoreFilter($this->getStoreId());
            }
        } else {
            $this->addWebsiteFilter($websiteIds);
        }

        return $this;
    }
}
