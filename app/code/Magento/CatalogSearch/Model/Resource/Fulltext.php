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
namespace Magento\CatalogSearch\Model\Resource;

/**
 * CatalogSearch Fulltext Index resource model
 */
class Fulltext extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Core string
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * CatalogSearch resource helper
     *
     * @var \Magento\CatalogSearch\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param Helper $resourceHelper
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\CatalogSearch\Model\Resource\Helper $resourceHelper
    ) {
        $this->_eventManager = $eventManager;
        $this->filter = $filter;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($resource);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_fulltext', 'product_id');
    }

    /**
     * Reset search results
     *
     * @param null|int $storeId
     * @param null|array $productIds
     * @return $this
     */
    public function resetSearchResults($storeId = null, $productIds = null)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->update($this->getTable('catalogsearch_query'), array('is_processed' => 0));

        if ($storeId === null && $productIds === null) {
            // Keeping public interface
            $adapter->update($this->getTable('catalogsearch_query'), array('is_processed' => 0));
            $adapter->delete($this->getTable('catalogsearch_result'));
            $this->_eventManager->dispatch('catalogsearch_reset_search_result');
        } else {
            // Optimized deletion only product-related records
            /** @var $select \Magento\Framework\DB\Select */
            $select = $adapter->select()->from(
                array('r' => $this->getTable('catalogsearch_result')),
                null
            )->join(
                array('q' => $this->getTable('catalogsearch_query')),
                'q.query_id=r.query_id',
                array()
            )->join(
                array('res' => $this->getTable('catalogsearch_result')),
                'q.query_id=res.query_id',
                array()
            );
            if (!empty($storeId)) {
                $select->where('q.store_id = ?', $storeId);
            }
            if (!empty($productIds)) {
                $select->where('r.product_id IN(?)', $productIds);
            }
            $query = $select->deleteFromSelect('res');
            $adapter->query($query);

            /** @var $select \Magento\Framework\DB\Select */
            $select = $adapter->select();
            $subSelect = $adapter->select()->from(array('res' => $this->getTable('catalogsearch_result')), null);
            $select->exists($subSelect, 'res.query_id=' . $this->getTable('catalogsearch_query') . '.query_id', false);

            $adapter->update(
                $this->getTable('catalogsearch_query'),
                array('is_processed' => 0),
                $select->getPart(\Zend_Db_Select::WHERE)
            );
        }

        return $this;
    }

    /**
     * Prepare results for query
     *
     * @param \Magento\CatalogSearch\Model\Fulltext $object
     * @param string $queryText
     * @param \Magento\CatalogSearch\Model\Query $query
     * @return $this
     */
    public function prepareResult($object, $queryText, $query)
    {
        $adapter = $this->_getWriteAdapter();
        if (!$query->getIsProcessed()) {
            $searchType = $object->getSearchType($query->getStoreId());

            $bind = array();
            $like = array();
            $likeCond = '';
            if ($searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_LIKE ||
                $searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_COMBINE
            ) {
                $words = $this->filter->splitWords(
                    $queryText,
                    array('uniqueOnly' => true, 'wordsQty' => $query->getMaxQueryWords())
                );
                foreach ($words as $word) {
                    $like[] = $this->_resourceHelper->getCILike('s.data_index', $word, array('position' => 'any'));
                }
                if ($like) {
                    $likeCond = '(' . join(' OR ', $like) . ')';
                }
            }
            $mainTableAlias = 's';
            $fields = array('query_id' => new \Zend_Db_Expr($query->getId()), 'product_id');
            $select = $adapter->select()->from(
                array($mainTableAlias => $this->getMainTable()),
                $fields
            )->joinInner(
                array('e' => $this->getTable('catalog_product_entity')),
                'e.entity_id = s.product_id',
                array()
            )->where(
                $mainTableAlias . '.store_id = ?',
                (int)$query->getStoreId()
            );

            $where = '';
            if ($searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_FULLTEXT ||
                $searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_COMBINE
            ) {
                $preparedTerms = $this->_resourceHelper->prepareTerms($queryText, $query->getMaxQueryWords());
                $bind[':query'] = implode(' ', $preparedTerms[0]);
                $where = $this->_resourceHelper->chooseFulltext($this->getMainTable(), $mainTableAlias, $select);
            }

            if ($likeCond != '' && $searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_COMBINE) {
                $where .= ($where ? ' OR ' : '') . $likeCond;
            } elseif ($likeCond != '' && $searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_LIKE) {
                $select->columns(array('relevance' => new \Zend_Db_Expr(0)));
                $where = $likeCond;
            }

            if ($where != '') {
                $select->where($where);
            }

            $sql = $adapter->insertFromSelect(
                $select,
                $this->getTable('catalogsearch_result'),
                array(),
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $adapter->query($sql, $bind);

            $query->setIsProcessed(1);
        }

        return $this;
    }
}
