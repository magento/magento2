<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

/**
 * Removes a store-scoped url_path row left behind once its url_key override has been reverted.
 */
class CategoryUrlPathOrphanOverrideCleanupObserver implements ObserverInterface
{
    /**
     * @var StoreViewService
     */
    private $storeViewService;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param StoreViewService $storeViewService
     * @param MetadataPool $metadataPool
     */
    public function __construct(StoreViewService $storeViewService, MetadataPool $metadataPool)
    {
        $this->storeViewService = $storeViewService;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        $isStoreScopedRevert = !$category->isObjectNew() && $category->getStoreId() !== Store::DEFAULT_STORE_ID;
        $useDefaultAttribute = !empty($category->getData('use_default')['url_key']);
        if (!$isStoreScopedRevert || !$useDefaultAttribute) {
            return;
        }

        $storeId = (int) $category->getStoreId();
        $categoryId = (int) $category->getId();
        if (!$this->storeViewService->doesEntityHaveOverriddenUrlPathForStore(
            $storeId,
            $categoryId,
            Category::ENTITY
        )) {
            return;
        }

        $linkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
        $resource = $category->getResource();
        $attribute = $resource->getAttribute('url_path');
        $resource->getConnection()->delete(
            $attribute->getBackendTable(),
            [
                'attribute_id = ?' => $attribute->getAttributeId(),
                $linkField . ' = ?' => $category->getData($linkField),
                'store_id = ?' => $storeId,
            ]
        );
    }
}
