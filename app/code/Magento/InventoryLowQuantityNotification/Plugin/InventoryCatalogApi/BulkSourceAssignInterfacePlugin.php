<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin\InventoryCatalogApi;

use Magento\InventoryCatalogApi\Api\BulkSourceAssignInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationAssign;

/**
 * This plugin keeps consistency between SourceItem and SourceItemConfiguration while bulk assigning
 */
class BulkSourceAssignInterfacePlugin
{
    /**
     * @var BulkConfigurationAssign
     */
    private $bulkConfigurationAssign;

    /**
     * @param BulkConfigurationAssign $bulkConfigurationAssign
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkConfigurationAssign $bulkConfigurationAssign
    ) {
        $this->bulkConfigurationAssign = $bulkConfigurationAssign;
    }

    /**
     * Keep database consistency while bulk assign items
     *
     * @param BulkSourceAssignInterface $subject
     * @param callable $proceed
     * @param array $skus
     * @param array $sources
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        BulkSourceAssignInterface $subject,
        callable $proceed,
        array $skus,
        array $sources
    ): int {
        $res = $proceed($skus, $sources);
        $this->bulkConfigurationAssign->execute($skus, $sources);
        return $res;
    }
}
