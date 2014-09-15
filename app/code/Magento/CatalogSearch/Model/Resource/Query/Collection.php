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
namespace Magento\CatalogSearch\Model\Resource\Query;

use Magento\Store\Model\Store;

/**
 * Catalog search query collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Store for filter
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog search resource helper
     *
     * @var \Magento\CatalogSearch\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\CatalogSearch\Model\Resource\Helper $resourceHelper
     * @param \Zend_Db_Adapter_Abstract $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\CatalogSearch\Model\Resource\Helper $resourceHelper,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Init model for collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CatalogSearch\Model\Query', 'Magento\CatalogSearch\Model\Resource\Query');
    }

    /**
     * Set Store ID for filter
     *
     * @param Store|int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        if ($store instanceof Store) {
            $store = $store->getId();
        }
        $this->_storeId = $store;
        return $this;
    }

    /**
     * Retrieve Store ID Filter
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Set search query text to filter
     *
     * @param string $query
     * @return $this
     */
    public function setQueryFilter($query)
    {
        $ifSynonymFor = $this->getConnection()->getIfNullSql('synonym_for', 'query_text');
        $this->getSelect()->reset(
            \Zend_Db_Select::FROM
        )->distinct(
            true
        )->from(
            array('main_table' => $this->getTable('catalogsearch_query')),
            array('query' => $ifSynonymFor, 'num_results')
        )->where(
            'num_results > 0 AND display_in_terms = 1 AND query_text LIKE ?',
            $this->_resourceHelper->addLikeEscape($query, array('position' => 'start'))
        )->order(
            'popularity ' . \Magento\Framework\DB\Select::SQL_DESC
        );
        if ($this->getStoreId()) {
            $this->getSelect()->where('store_id = ?', (int)$this->getStoreId());
        }
        return $this;
    }

    /**
     * Set Popular Search Query Filter
     *
     * @param int|array $storeIds
     * @return $this
     */
    public function setPopularQueryFilter($storeIds = null)
    {
        $ifSynonymFor = new \Zend_Db_Expr(
            $this->getConnection()->getCheckSql(
                "synonym_for IS NOT NULL AND synonym_for != ''",
                'synonym_for',
                'query_text'
            )
        );

        $this->getSelect()->reset(
            \Zend_Db_Select::FROM
        )->reset(
            \Zend_Db_Select::COLUMNS
        )->distinct(
            true
        )->from(
            array('main_table' => $this->getTable('catalogsearch_query')),
            array('name' => $ifSynonymFor, 'num_results', 'popularity')
        );
        if ($storeIds) {
            $this->addStoreFilter($storeIds);
            $this->getSelect()->where('num_results > 0');
        } elseif (null === $storeIds) {
            $this->addStoreFilter($this->_storeManager->getStore()->getId());
            $this->getSelect()->where('num_results > 0');
        }

        $this->getSelect()->order(array('popularity desc', 'name'));

        return $this;
    }

    /**
     * Set Recent Queries Order
     *
     * @return $this
     */
    public function setRecentQueryFilter()
    {
        $this->setOrder('updated_at', 'desc');
        return $this;
    }

    /**
     * Filter collection by specified store ids
     *
     * @param array|int $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }
        $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        return $this;
    }
}
