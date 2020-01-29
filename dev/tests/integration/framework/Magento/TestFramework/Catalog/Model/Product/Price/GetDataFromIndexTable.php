<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Product\Price;

use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

/**
 * Search and return price data from price index table.
 */
class GetDataFromIndexTable
{
    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @param ProductResource $productResource
     */
    public function __construct(
        ProductResource $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * Returns price data by product id.
     *
     * @param int $productId
     * @param int|null $groupId
     * @param int|null $websiteId
     * @return array
     */
    public function execute(int $productId, ?int $groupId = null, ?int $websiteId = null): array
    {
        $select = $this->productResource->getConnection()->select()
            ->from($this->productResource->getTable(TableMaintainer::MAIN_INDEX_TABLE))
            ->where('entity_id = ?', $productId);
        if (isset($groupId)) {
            $select->where('customer_group_id = ?', $groupId);
        }
        if (isset($websiteId)) {
            $select->where('website_id = ?', $websiteId);
        }

        return $this->productResource->getConnection()->fetchAll($select);
    }
}
