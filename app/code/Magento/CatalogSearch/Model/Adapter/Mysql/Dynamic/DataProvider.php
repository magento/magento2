<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Dynamic;

use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
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
     * @param Resource $resource
     * @param Range $range
     * @param Session $customerSession
     * @param MysqlDataProviderInterface $dataProvider
     * @param IntervalFactory $intervalFactory
     */
    public function __construct(
        Resource $resource,
        Range $range,
        Session $customerSession,
        MysqlDataProviderInterface $dataProvider,
        IntervalFactory $intervalFactory
    ) {
        $this->resource = $resource;
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
    public function getAggregations(array $entityIds)
    {
        $aggregation = [
            'count' => 'count(DISTINCT entity_id)',
            'max' => 'MAX(min_price)',
            'min' => 'MIN(min_price)',
            'std' => 'STDDEV_SAMP(min_price)'
        ];

        $select = $this->getSelect();

        $tableName = $this->resource->getTableName('catalog_product_index_price');
        $select->from($tableName, [])
            ->where('entity_id IN (?)', $entityIds)
            ->columns($aggregation);

        $select = $this->setCustomerGroupId($select);

        $result = $this->getConnection()
            ->fetchRow($select);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval(BucketInterface $bucket, array $dimensions, array $entityIds)
    {
        $select = $this->dataProvider->getDataSet($bucket, $dimensions);
        $select->where('main_table.entity_id IN (?)', $entityIds);

        return $this->intervalFactory->create(['select' => $select]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregation(BucketInterface $bucket, array $dimensions, $range, array $entityIds)
    {
        $select = $this->dataProvider->getDataSet($bucket, $dimensions);
        $column = $select->getPart(Select::COLUMNS)[0];
        $select->reset(Select::COLUMNS);
        $rangeExpr = new \Zend_Db_Expr(
            $this->getConnection()
                ->quoteInto('(FLOOR(' . $column[1] . ' / ? ) + 1)', $range)
        );

        $select
            ->columns(['range' => $rangeExpr])
            ->columns(['metrix' => 'COUNT(*)'])
            ->where('main_table.entity_id in (?)', $entityIds)
            ->group('range')
            ->order('range');
        $result = $this->getConnection()
            ->fetchPairs($select);

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
                    'count' => $count
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
        return $this->getConnection()
            ->select();
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
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
