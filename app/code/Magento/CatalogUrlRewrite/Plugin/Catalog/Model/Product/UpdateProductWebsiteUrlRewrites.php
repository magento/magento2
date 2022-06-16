<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Model\Product;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\Products\AppendUrlRewritesToProducts;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\StoreResolver\GetStoresListByWebsiteIds;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Update URL rewrites after website change
 */
class UpdateProductWebsiteUrlRewrites
{
    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var AppendUrlRewritesToProducts
     */
    private $appendRewrites;

    /**
     * @var GetStoresListByWebsiteIds
     */
    private $getStoresList;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param CollectionFactory $productCollectionFactory
     * @param AppendUrlRewritesToProducts $appendRewrites
     * @param GetStoresListByWebsiteIds $getStoresList
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        CollectionFactory $productCollectionFactory,
        AppendUrlRewritesToProducts $appendRewrites,
        GetStoresListByWebsiteIds $getStoresList
    ) {
        $this->urlPersist = $urlPersist;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->appendRewrites = $appendRewrites;
        $this->getStoresList = $getStoresList;
    }

    /**
     * Update url rewrites after website changes
     *
     * @param ProductAction $subject
     * @param void $result
     * @param array $productIds
     * @param array $websiteIds
     * @param string $type
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateWebsites(
        ProductAction $subject,
        $result,
        array $productIds,
        array $websiteIds,
        string $type
    ): void {
        if (empty($websiteIds)) {
            return;
        }
        $storeIds = $this->getStoresList->execute($websiteIds);
        // Remove the URLs from websites this product no longer belongs to
        if ($type == 'remove') {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $productIds,
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $storeIds,
                ]
            );
        } else {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addFieldToFilter('entity_id', ['in' => implode(',', $productIds)]);
            $this->appendRewrites->execute($productCollection->getItems(), $storeIds);
        }
    }
}
