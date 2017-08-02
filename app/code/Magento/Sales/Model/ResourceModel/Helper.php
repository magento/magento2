<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

use Magento\Sales\Model\ResourceModel\Report\Bestsellers as BestsellersReport;

/**
 * Sales Mysql resource helper model
 * @since 2.0.0
 */
class Helper extends \Magento\Framework\DB\Helper implements HelperInterface
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Helper
     * @since 2.0.0
     */
    protected $_reportsResourceHelper;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Reports\Model\ResourceModel\Helper $reportsResourceHelper
     * @param string $modulePrefix
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Reports\Model\ResourceModel\Helper $reportsResourceHelper,
        $modulePrefix = 'sales'
    ) {
        parent::__construct($resource, $modulePrefix);
        $this->_reportsResourceHelper = $reportsResourceHelper;
    }

    /**
     * Update rating position
     *
     * @param string $aggregation One of BestsellersReport::AGGREGATION_XXX constants
     * @param array $aggregationAliases
     * @param string $mainTable
     * @param string $aggregationTable
     * @return $this
     * @since 2.0.0
     */
    public function getBestsellersReportUpdateRatingPos(
        $aggregation,
        $aggregationAliases,
        $mainTable,
        $aggregationTable
    ) {
        $connection = $this->_resource->getConnection('sales');
        if ($aggregation == $aggregationAliases['monthly']) {
            $this->_reportsResourceHelper->updateReportRatingPos(
                $connection,
                'month',
                'qty_ordered',
                $mainTable,
                $aggregationTable
            );
        } elseif ($aggregation == $aggregationAliases['yearly']) {
            $this->_reportsResourceHelper->updateReportRatingPos(
                $connection,
                'year',
                'qty_ordered',
                $mainTable,
                $aggregationTable
            );
        } else {
            $this->_reportsResourceHelper->updateReportRatingPos(
                $connection,
                'day',
                'qty_ordered',
                $mainTable,
                $aggregationTable
            );
        }

        return $this;
    }
}
