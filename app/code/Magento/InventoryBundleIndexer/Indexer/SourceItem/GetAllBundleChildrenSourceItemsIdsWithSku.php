<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Get only bundle children source items ids.
 */
class GetAllBundleChildrenSourceItemsIdsWithSku
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var BundleChildrenSourceItemsIdsSelectProvider
     */
    private $bundleChildrenSourceItemsIdsSelectProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param BundleChildrenSourceItemsIdsSelectProvider $bundleChildrenSourceItemsIdsSelectProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        BundleChildrenSourceItemsIdsSelectProvider $bundleChildrenSourceItemsIdsSelectProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->bundleChildrenSourceItemsIdsSelectProvider = $bundleChildrenSourceItemsIdsSelectProvider;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $select = $this->bundleChildrenSourceItemsIdsSelectProvider->execute();
        $select->where('bundle_product.' . ProductInterface::TYPE_ID . ' = ?', ProductType::TYPE_BUNDLE);

        $bundleChildren = $select->query()->fetchAll();
        $bundleChildrenSourceItemsIdsBySku = [];

        foreach ($bundleChildren as $bundleChild) {
            $bundleChildrenSourceItemsIdsBySku[$bundleChild['sku']][] = $bundleChild[SourceItem::ID_FIELD_NAME];
        }

        return $bundleChildrenSourceItemsIdsBySku;
    }
}
