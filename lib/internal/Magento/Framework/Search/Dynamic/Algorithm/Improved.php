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
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\IntervalFactory;
use Magento\Framework\Search\Request\BucketInterface;

class Improved extends AbstractAlgorithm
{
    /**
     * @var Algorithm
     */
    private $algorithm;

    /**
     * @var IntervalFactory
     */
    private $intervalFactory;

    /**
     * @param DataProviderInterface $dataProvider
     * @param Algorithm $algorithm
     * @param IntervalFactory $intervalFactory
     */
    public function __construct(
        DataProviderInterface $dataProvider,
        Algorithm $algorithm,
        IntervalFactory $intervalFactory
    ) {
        parent::__construct($dataProvider);
        $this->algorithm = $algorithm;
        $this->intervalFactory = $intervalFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(BucketInterface $bucket, array $dimensions, array $entityIds)
    {
        $aggregations = $this->dataProvider->getAggregations($entityIds);

        $options = $this->dataProvider->getOptions();
        if ($aggregations['count'] < $options['interval_division_limit']) {
            return [];
        }
        $select = $this->dataProvider->getDataSet($bucket, $dimensions);
        $select->where('main_table.entity_id IN (?)', $entityIds);

        $interval = $this->intervalFactory->create(['select' => $select]);
        $this->algorithm->setStatistics(
            $aggregations['min'],
            $aggregations['max'],
            $aggregations['std'],
            $aggregations['count']
        );

        return $this->algorithm->calculateSeparators($interval);
    }
}
