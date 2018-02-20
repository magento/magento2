<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

/**
 * Source Item indexer
 * Check bundle children, if one of them in_stock - make bundle in_stock
 *
 * @api
 */
class SourceItemIndexer
{
    /**
     * @var GetAllBundleChildrenSourceItemsIdsWithSku
     */
    private $getAllBundleChildrenSourceItemsIdsWithSku;

    /**
     * @var GetBundleChildrenSourceItemsIdsWithSku
     */
    private $getBundleChildrenSourceItemsIdsWithSku;

    /**
     * @var BundleBySkuAndChildrenSourceItemsIdsIndexer
     */
    private $bundleBySkuAndChildrenSourceItemsIdsIndexer;

    /**
     * @param GetAllBundleChildrenSourceItemsIdsWithSku $getAllBundleChildrenSourceItemsIdsWithSku
     * @param GetBundleChildrenSourceItemsIdsWithSku $getBundleChildrenSourceItemsIdsWithSku
     * @param BundleBySkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer
     */
    public function __construct(
        GetAllBundleChildrenSourceItemsIdsWithSku $getAllBundleChildrenSourceItemsIdsWithSku,
        GetBundleChildrenSourceItemsIdsWithSku $getBundleChildrenSourceItemsIdsWithSku,
        BundleBySkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer
    ) {
        $this->getAllBundleChildrenSourceItemsIdsWithSku = $getAllBundleChildrenSourceItemsIdsWithSku;
        $this->getBundleChildrenSourceItemsIdsWithSku = $getBundleChildrenSourceItemsIdsWithSku;
        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer = $bundleBySkuAndChildrenSourceItemsIdsIndexer;
    }

    /**
     * @return void
     */
    public function executeFull()
    {
        $bundleChildrenSourceItemsIdsWithSku = $this->getAllBundleChildrenSourceItemsIdsWithSku->execute();

        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer->execute($bundleChildrenSourceItemsIdsWithSku);
    }

    /**
     * @param int $sourceItemId
     * @return void
     */
    public function executeRow(int $sourceItemId)
    {
        $this->executeList([$sourceItemId]);
    }

    /**
     * @param array $sourceItemIds
     * @return void
     */
    public function executeList(array $sourceItemIds)
    {
        $bundleChildrenSourceItemsIdsWithSku = $this->getBundleChildrenSourceItemsIdsWithSku->execute($sourceItemIds);

        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer->execute($bundleChildrenSourceItemsIdsWithSku);
    }
}
