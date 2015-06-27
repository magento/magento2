<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource;

/**
 * Sales Mysql resource helper model
 */
class Helper extends \Magento\Framework\DB\Helper implements HelperInterface
{
    /**
     * @var \Magento\Reports\Model\Resource\Helper
     */
    protected $_reportsResourceHelper;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Reports\Model\Resource\Helper $reportsResourceHelper
     * @param string $modulePrefix
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Reports\Model\Resource\Helper $reportsResourceHelper,
        $modulePrefix = 'sales'
    ) {
        parent::__construct($resource, $modulePrefix);
        $this->_reportsResourceHelper = $reportsResourceHelper;
    }

    /**
     * Update rating position
     *
     * @param string $aggregation One of \Magento\Sales\Model\Resource\Report\Bestsellers::AGGREGATION_XXX constants
     * @param array $aggregationAliases
     * @param string $mainTable
     * @param string $aggregationTable
     * @return $this
     */
    public function getBestsellersReportUpdateRatingPos(
        $aggregation,
        $aggregationAliases,
        $mainTable,
        $aggregationTable
    ) {
        $adapter = $this->_resource->getConnection('sales_write');
        if ($aggregation == $aggregationAliases['monthly']) {
            $this->_reportsResourceHelper->updateReportRatingPos(
                $adapter,
                'month',
                'qty_ordered',
                $mainTable,
                $aggregationTable
            );
        } elseif ($aggregation == $aggregationAliases['yearly']) {
            $this->_reportsResourceHelper->updateReportRatingPos(
                $adapter,
                'year',
                'qty_ordered',
                $mainTable,
                $aggregationTable
            );
        } else {
            $this->_reportsResourceHelper->updateReportRatingPos(
                $adapter,
                'day',
                'qty_ordered',
                $mainTable,
                $aggregationTable
            );
        }

        return $this;
    }
}
