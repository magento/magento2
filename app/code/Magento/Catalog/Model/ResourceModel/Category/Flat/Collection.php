<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category\Flat;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface as Logger;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Catalog category flat collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'catalog_category_collection';

    /**
     * Event object name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'category_collection';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Store id of application
     *
     * @var integer
     * @since 2.0.0
     */
    protected $_storeId;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param AbstractDb $resource
     * @since 2.0.0
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Collection initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Catalog\Model\Category::class,
            \Magento\Catalog\Model\ResourceModel\Category\Flat::class
        );
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(
            ['main_table' => $this->getResource()->getMainStoreTable($this->getStoreId())],
            ['entity_id', 'level', 'path', 'position', 'is_active', 'is_anchor']
        );
        return $this;
    }

    /**
     * Add filter by entity id(s).
     *
     * @param array|int|string $categoryIds
     * @return $this
     * @since 2.0.0
     */
    public function addIdFilter($categoryIds)
    {
        if (is_array($categoryIds)) {
            if (empty($categoryIds)) {
                $condition = '';
            } else {
                $condition = ['in' => $categoryIds];
            }
        } elseif (is_numeric($categoryIds)) {
            $condition = $categoryIds;
        } elseif (is_string($categoryIds)) {
            $ids = explode(',', $categoryIds);
            if (empty($ids)) {
                $condition = $categoryIds;
            } else {
                $condition = ['in' => $ids];
            }
        }
        $this->addFieldToFilter('main_table.entity_id', $condition);
        return $this;
    }

    /**
     * Before collection load
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeLoad()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_before', [$this->_eventObject => $this]);
        return parent::_beforeLoad();
    }

    /**
     * After collection load
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _afterLoad()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_after', [$this->_eventObject => $this]);
        return parent::_afterLoad();
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Return store id.
     * If store id is not set yet, return store of application
     *
     * @return integer
     * @since 2.0.0
     */
    public function getStoreId()
    {
        if (null === $this->_storeId) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Add filter by path to collection
     *
     * @param string $parent
     * @return $this
     * @since 2.0.0
     */
    public function addParentPathFilter($parent)
    {
        $this->addFieldToFilter('path', ['like' => "{$parent}/%"]);
        return $this;
    }

    /**
     * Add store filter
     *
     * @return $this
     * @since 2.0.0
     */
    public function addStoreFilter()
    {
        $this->addFieldToFilter('main_table.store_id', $this->getStoreId());
        return $this;
    }

    /**
     * Set field to sort by
     *
     * @param string $sorted
     * @return $this
     * @since 2.0.0
     */
    public function addSortedField($sorted)
    {
        if (is_string($sorted)) {
            $this->addOrder($sorted, self::SORT_ORDER_ASC);
        } else {
            $this->addOrder('name', self::SORT_ORDER_ASC);
        }
        return $this;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function addIsActiveFilter()
    {
        $this->addFieldToFilter('is_active', 1);
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_add_is_active_filter',
            [$this->_eventObject => $this]
        );
        return $this;
    }

    /**
     * Add name field to result
     *
     * @return $this
     * @since 2.0.0
     */
    public function addNameToResult()
    {
        $this->addAttributeToSelect('name');
        return $this;
    }

    /**
     * Add attribute to select
     *
     * @param array|string $attribute
     * @return $this
     * @since 2.0.0
     */
    public function addAttributeToSelect($attribute = '*')
    {
        if ($attribute == '*') {
            // Save previous selected columns
            $columns = $this->getSelect()->getPart(\Magento\Framework\DB\Select::COLUMNS);
            $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
            foreach ($columns as $column) {
                if ($column[0] == 'main_table') {
                    // If column selected from main table,
                    // no need to select it again
                    continue;
                }

                // Joined columns
                if ($column[2] !== null) {
                    $expression = [$column[2] => $column[1]];
                } else {
                    $expression = $column[2];
                }
                $this->getSelect()->columns($expression, $column[0]);
            }

            $this->getSelect()->columns('*', 'main_table');
            return $this;
        }

        if (!is_array($attribute)) {
            $attribute = [$attribute];
        }

        $this->getSelect()->columns($attribute, 'main_table');
        return $this;
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Flat
     * @since 2.0.0
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     * @since 2.0.0
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (!is_string($attribute)) {
            return $this;
        }
        $this->setOrder($attribute, $dir);
        return $this;
    }

    /**
     * Emulate simple add attribute filter to collection
     *
     * @param string $attribute
     * @param null|string|array $condition
     * @return $this
     * @since 2.0.0
     */
    public function addAttributeToFilter($attribute, $condition = null)
    {
        if (!is_string($attribute) || $condition === null) {
            return $this;
        }

        return $this->addFieldToFilter($attribute, $condition);
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function addUrlRewriteToResult()
    {
        $connection = $this->getConnection();

        $this->getSelect()->joinLeft(
            ['url_rewrite' => $this->getTable('url_rewrite')],
            'url_rewrite.entity_id = main_table.entity_id AND url_rewrite.is_autogenerated = 1'
            . $connection->quoteInto(' AND url_rewrite.store_id = ?', $this->_storeManager->getStore()->getId())
            . $connection->quoteInto(' AND url_rewrite.entity_type = ?', CategoryUrlRewriteGenerator::ENTITY_TYPE),
            ['request_path']
        );
        return $this;
    }

    /**
     * @param string|array $paths
     * @return $this
     * @since 2.0.0
     */
    public function addPathsFilter($paths)
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        $select = $this->getSelect();
        $orWhere = false;
        foreach ($paths as $path) {
            if ($orWhere) {
                $select->orWhere('main_table.path LIKE ?', "{$path}%");
            } else {
                $select->where('main_table.path LIKE ?', "{$path}%");
                $orWhere = true;
            }
        }
        return $this;
    }

    /**
     * @param string $level
     * @return $this
     * @since 2.0.0
     */
    public function addLevelFilter($level)
    {
        $this->getSelect()->where('main_table.level <= ?', $level);
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     * @since 2.0.0
     */
    public function addOrderField($field)
    {
        $this->setOrder('main_table.' . $field, self::SORT_ORDER_ASC);
        return $this;
    }

    /**
     * Set collection page start and records to show
     *
     * @param integer $pageNum
     * @param integer $pageSize
     * @return $this
     * @since 2.0.0
     */
    public function setPage($pageNum, $pageSize)
    {
        $this->setCurPage($pageNum)->setPageSize($pageSize);
        return $this;
    }
}
