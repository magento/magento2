<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Dynamic;

use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface as MysqlDataProviderInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var Range
     */
    private $range;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var MysqlDataProviderInterface
     */
    private $dataProvider;

    /**
     * @var IntervalFactory
     */
    private $intervalFactory;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param ResourceConnection $resource
     * @param Range $range
     * @param Session $customerSession
     * @param MysqlDataProviderInterface $dataProvider
     * @param IntervalFactory $intervalFactory
     */
    public function __construct(
        ResourceConnection $resource,
        Range $range,
        Session $customerSession,
        MysqlDataProviderInterface $dataProvider,
        IntervalFactory $intervalFactory
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->range = $range;
        $this->customerSession = $customerSession;
        $this->dataProvider = $dataProvider;
        $this->intervalFactory = $intervalFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations(\Magento\Framework\Search\Dynamic\EntityStorage $entityStorage)
    {
        $aggregation = [
            'count' => 'count(DISTINCT main_table.entity_id)',
            'max' => 'MAX(min_price)',
            'min' => 'MIN(min_price)',
            'std' => 'STDDEV_SAMP(min_price)',
        ];

        $select = $this->getSelect();

        $tableName = $this->resource->getTableName('catalog_product_index_price');
        /** @var Table $table */
        $table = $entityStorage->getSource();
        $select->from(['main_table' => $tableName], [])
            ->joinInner(['entities' => $table->getName()], 'main_table.entity_id  = entities.entity_id', [])
            ->columns($aggregation);

        $select = $this->setCustomerGroupId($select);

        $result = $this->connection->fetchRow($select);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        $select = $this->dataProvider->getDataSet($bucket, $dimensions, $entityStorage->getSource());

        return $this->intervalFactory->create(['select' => $select]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        $select = $this->dataProvider->getDataSet($bucket, $dimensions, $entityStorage->getSource());
        $column = $select->getPart(Select::COLUMNS)[0];
        $select->reset(Select::COLUMNS);
        $rangeExpr = new \Zend_Db_Expr(
            $this->connection->getIfNullSql(
                $this->connection->quoteInto('FLOOR(' . $column[1] . ' / ? ) + 1', $range),
                1
            )
        );

        $select
            ->columns(['range' => $rangeExpr])
            ->columns(['metrix' => 'COUNT(*)'])
            ->group('range')
            ->order('range');
        $result = $this->connection->fetchPairs($select);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($range, array $dbRanges)
    {
        $data = [];
        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];

            foreach ($dbRanges as $index => $count) {
                $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
                $toPrice = $index == $lastIndex ? '' : $index * $range;

                $data[] = [
                    'from' => $fromPrice,
                    'to' => $toPrice,
                    'count' => $count,
                ];
            }
        }

        return $data;
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }

    /**
     * @param Select $select
     * @return Select
     */
    private function setCustomerGroupId($select)
    {
        return $select->where('customer_group_id = ?', $this->customerSession->getCustomerGroupId());
    }
}
