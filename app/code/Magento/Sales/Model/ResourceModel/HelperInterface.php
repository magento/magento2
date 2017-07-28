<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

use Magento\Sales\Model\ResourceModel\Report\Bestsellers as BestsellersReport;

/**
 * Sales resource helper interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
interface HelperInterface
{
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
    );
}
