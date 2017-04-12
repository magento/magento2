<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Reports resource helper interface
 */
namespace Magento\Reports\Model\ResourceModel;

interface HelperInterface
{
    /**
     * Merge Index data
     *
     * @param string $mainTable
     * @param array $data
     * @param mixed $matchFields
     * @return string
     */
    public function mergeVisitorProductIndex($mainTable, $data, $matchFields);

    /**
     * Update rating position
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $type
     * @param string $column
     * @param string $mainTable
     * @param string $aggregationTable
     * @return $this
     */
    public function updateReportRatingPos($connection, $type, $column, $mainTable, $aggregationTable);
}
