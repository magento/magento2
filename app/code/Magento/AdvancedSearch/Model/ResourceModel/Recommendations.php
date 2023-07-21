<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Framework\DB\Select;
use Zend_Db_Expr;

/**
 * Catalog search recommendations resource model
 *
 * @api
 * @since 100.0.2
 */
class Recommendations extends AbstractDb
{

    /**
     * @var Query
     */
    protected $_searchQueryModel;

    /**
     * Construct
     *
     * @param DbContext $context
     * @param QueryFactory $queryFactory
     * @param string $connectionName
     */
    public function __construct(
        DbContext $context,
        QueryFactory $queryFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_searchQueryModel = $queryFactory->create();
    }

    /**
     * Init main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_recommendations', 'id');
    }

    /**
     * Save search relations
     *
     * @param int $queryId
     * @param array $relatedQueries
     * @return $this
     */
    public function saveRelatedQueries($queryId, $relatedQueries = [])
    {
        $connection = $this->getConnection();
        $whereOr = [];
        if (count($relatedQueries) > 0) {
            $whereOr[] = implode(
                ' AND ',
                [
                    $connection->quoteInto('query_id=?', $queryId),
                    $connection->quoteInto('relation_id NOT IN(?)', $relatedQueries)
                ]
            );
            $whereOr[] = implode(
                ' AND ',
                [
                    $connection->quoteInto('relation_id = ?', $queryId),
                    $connection->quoteInto('query_id NOT IN(?)', $relatedQueries)
                ]
            );
        } else {
            $whereOr[] = $connection->quoteInto('query_id = ?', $queryId);
            $whereOr[] = $connection->quoteInto('relation_id = ?', $queryId);
        }
        $whereCond = '(' . implode(') OR (', $whereOr) . ')';
        $connection->delete($this->getMainTable(), $whereCond);

        $existsRelatedQueries = $this->getRelatedQueries($queryId);
        $neededRelatedQueries = array_diff($relatedQueries, $existsRelatedQueries);
        foreach ($neededRelatedQueries as $relationId) {
            $connection->insert($this->getMainTable(), ["query_id" => $queryId, "relation_id" => $relationId]);
        }
        return $this;
    }

    /**
     * Retrieve related search queries
     *
     * @param int|array $queryId
     * @param bool $limit
     * @param bool $order
     * @return array
     */
    public function getRelatedQueries($queryId, $limit = false, $order = false)
    {
        $collection = $this->_searchQueryModel->getResourceCollection();
        $connection = $this->getConnection();

        $queryIdCond = $connection->quoteInto('main_table.query_id IN (?)', $queryId);

        $collection->getSelect()->join(
            ['sr' => $collection->getTable('catalogsearch_recommendations')],
            '(sr.query_id=main_table.query_id OR sr.relation_id=main_table.query_id) AND ' . $queryIdCond
        )->reset(
            Select::COLUMNS
        )->columns(
            [
                'rel_id' => $connection->getCheckSql(
                    'main_table.query_id=sr.query_id',
                    'sr.relation_id',
                    'sr.query_id'
                ),
            ]
        );
        if (!empty($limit)) {
            $collection->getSelect()->limit($limit);
        }
        if (!empty($order)) {
            $collection->getSelect()->order($order);
        }

        $queryIds = $connection->fetchCol($collection->getSelect());
        return $queryIds;
    }

    /**
     * Retrieve related search queries by single query
     *
     * @param string $query
     * @param array $params
     * @param int $searchRecommendationsCount
     * @return array
     */
    public function getRecommendationsByQuery($query, $params, $searchRecommendationsCount)
    {
        $this->_searchQueryModel->loadByQueryText($query);

        if (isset($params['store_id'])) {
            $this->_searchQueryModel->setStoreId($params['store_id']);
        }
        $relatedQueriesIds = $this->loadByQuery($query, $searchRecommendationsCount);
        $relatedQueries = [];
        if (count($relatedQueriesIds)) {
            $connection = $this->getConnection();
            $mainTable = $this->_searchQueryModel->getResourceCollection()->getMainTable();
            $select = $connection->select()->from(
                ['main_table' => $mainTable],
                ['query_text', 'num_results']
            )->where(
                'query_id IN(?)',
                $relatedQueriesIds
            )->where(
                'num_results > 0'
            );
            $relatedQueries = $connection->fetchAll($select);
        }

        return $relatedQueries;
    }

    /**
     * Retrieve search terms which are started with $queryWords
     *
     * @param string $query
     * @param int $searchRecommendationsCount
     * @return array
     */
    protected function loadByQuery($query, $searchRecommendationsCount)
    {
        $connection = $this->getConnection();
        $queryId = $this->_searchQueryModel->getId();
        $relatedQueries = $this->getRelatedQueries($queryId, $searchRecommendationsCount, 'num_results DESC');
        if ($searchRecommendationsCount - count($relatedQueries) < 1) {
            return $relatedQueries;
        }

        $queryWords = [$query];
        if ($query !== null && strpos($query, ' ') !== false) {
            $queryWords = array_unique(array_merge($queryWords, explode(' ', $query)));
            foreach ($queryWords as $key => $word) {
                $queryWords[$key] = trim($word);
                if (strlen($word) < 3) {
                    unset($queryWords[$key]);
                }
            }
        }

        $likeCondition = [];
        foreach ($queryWords as $word) {
            $likeCondition[] = $connection->quoteInto('query_text LIKE ?', $word . '%');
        }
        $likeCondition = implode(' OR ', $likeCondition);

        $select = $connection->select()->from(
            $this->_searchQueryModel->getResource()->getMainTable(),
            ['query_id']
        )->where(
            new Zend_Db_Expr($likeCondition)
        )->where(
            'store_id=?',
            $this->_searchQueryModel->getStoreId()
        )->order(
            'num_results DESC'
        )->limit(
            $searchRecommendationsCount + 1
        );
        $ids = $connection->fetchCol($select);

        if (!is_array($ids)) {
            $ids = [];
        }

        $key = array_search($queryId, $ids);
        if ($key !== false) {
            unset($ids[$key]);
        }
        $ids = array_unique(array_merge($relatedQueries, $ids));
        $ids = array_slice($ids, 0, $searchRecommendationsCount);
        return $ids;
    }
}
