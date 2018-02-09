<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Inventory\Model\ResourceModel\SourceItem;

/**
 * Get only bundle source items ids by source items ids.
 * It also include source items id of parent if parent product is bundle.
 */
class GetBundleSourceItemsIds
{
    /**
     * @var GetSourceItemsIdsWithProductTypeIds
     */
    private $getSourceItemsIdsWithProductTypeIds;

    /**
     * @var GetBundleSourceItemIdByChildSourceItemId
     */
    private $getBundleSourceItemIdByChildSourceItemId;

    /**
     * @param GetSourceItemsIdsWithProductTypeIds $getSourceItemsIdsWithProductTypeIds
     * @param GetBundleSourceItemIdByChildSourceItemId $getBundleSourceItemIdByChildSourceItemId
     */
    public function __construct(
        GetSourceItemsIdsWithProductTypeIds $getSourceItemsIdsWithProductTypeIds,
        GetBundleSourceItemIdByChildSourceItemId $getBundleSourceItemIdByChildSourceItemId
    ) {
        $this->getSourceItemsIdsWithProductTypeIds = $getSourceItemsIdsWithProductTypeIds;
        $this->getBundleSourceItemIdByChildSourceItemId = $getBundleSourceItemIdByChildSourceItemId;
    }

    /**
     * @param array $sourceItemIds
     *
     * @return array
     */
    public function execute(array $sourceItemIds): array
    {
        $sourceItemIdsBundleProduct = [];
        $sourceItemsIdsWithTypeIds = $this->getSourceItemsIdsWithProductTypeIds->execute($sourceItemIds);

        foreach ($sourceItemsIdsWithTypeIds as $sourceItemIdWithTypeId) {
            $sourceItemId = $sourceItemIdWithTypeId[SourceItem::ID_FIELD_NAME];

            if ($sourceItemIdWithTypeId[ProductInterface::TYPE_ID] == ProductType::TYPE_BUNDLE) {
                $sourceItemIdsBundleProduct[] = $sourceItemId;
            } else {
                $bundleSourceItemId = $this->getBundleSourceItemIdByChildSourceItemId->execute((int)$sourceItemId);
                if (null !== $bundleSourceItemId) {
                    $sourceItemIdsBundleProduct[] = $bundleSourceItemId;
                }
            }
        }

        return $sourceItemIdsBundleProduct;
    }
}
