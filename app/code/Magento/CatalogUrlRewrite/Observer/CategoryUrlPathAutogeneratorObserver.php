<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\GetDefaultUrlKey;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;

/**
 * Class for set or update url path.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class CategoryUrlPathAutogeneratorObserver implements ObserverInterface
{

    /**
     * @var CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var ChildrenCategoriesProvider
     */
    protected $childrenCategoriesProvider;

    /**
     * @var StoreViewService
     */
    protected $storeViewService;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CompositeUrlKey
     */
    private $compositeUrlValidator;

    /**
     * @var GetDefaultUrlKey
     */
    private $getDefaultUrlKey;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param StoreViewService $storeViewService
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CompositeUrlKey $compositeUrlValidator
     * @param GetDefaultUrlKey $getDefaultUrlKey
     * @param MetadataPool|null $metadataPool
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        StoreViewService $storeViewService,
        CategoryRepositoryInterface $categoryRepository,
        CompositeUrlKey $compositeUrlValidator,
        GetDefaultUrlKey $getDefaultUrlKey,
        ?MetadataPool $metadataPool = null
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->storeViewService = $storeViewService;
        $this->categoryRepository = $categoryRepository;
        $this->compositeUrlValidator = $compositeUrlValidator;
        $this->getDefaultUrlKey = $getDefaultUrlKey;
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()
            ->get(MetadataPool::class);
    }

    /**
     * Method for update/set url path.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        $useDefaultAttribute = !empty($category->getData('use_default')['url_key']);
        if ($category->getUrlKey() !== false && !$useDefaultAttribute) {
            $resultUrlKey = $this->categoryUrlPathGenerator->getUrlKey($category);
            $this->updateUrlKey($category, $resultUrlKey);
        } elseif ($useDefaultAttribute) {
            if (!$category->isObjectNew() && $category->getStoreId() === Store::DEFAULT_STORE_ID) {
                $resultUrlKey = $category->formatUrlKey($category->getOrigData('name'));
                $this->updateUrlKey($category, $resultUrlKey);
            }
            $this->restoreDefaultUrlKeyIfNeeded($category);
        }
    }

    /**
     * Restore url_key/url_path from the default-scope value.
     *
     * Runs for categories with children (to cascade the default to descendants) and for
     * categories reverted to the default url_key at a specific store view, including leaf
     * categories without children, which the default-store-scope branch above never handles.
     *
     * @param Category $category
     * @return void
     * @throws LocalizedException
     */
    private function restoreDefaultUrlKeyIfNeeded(Category $category): void
    {
        $isStoreScopedRevert = !$category->isObjectNew() && $category->getStoreId() !== Store::DEFAULT_STORE_ID;
        if (!$category->hasChildren()) {
            $hasStoreScopedOverride = $isStoreScopedRevert
                && $this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(
                    $category->getStoreId(),
                    $category->getId(),
                    Category::ENTITY
                );
            if (!$hasStoreScopedOverride) {
                return;
            }
        }
        $metadata = $this->metadataPool->getMetadata(CategoryInterface::class);
        $linkField = $metadata->getLinkField();
        $id = $category->getData($linkField);
        if (!$id) {
            return;
        }
        $defaultUrlKey = $this->getDefaultUrlKey->execute((int)$id);
        if (!$defaultUrlKey) {
            return;
        }
        if ($isStoreScopedRevert) {
            $this->removeStoreScopedAttributeOverride($category, $linkField, 'url_key');
        }
        $this->updateUrlKey($category, $defaultUrlKey, $isStoreScopedRevert);
        if ($isStoreScopedRevert) {
            $category->setUrlKey(null);
        }
    }

    /**
     * Remove a store-scoped attribute override row directly, without disturbing other stores.
     *
     * Category's resource model (unlike Product's) does not scope saveAttribute()/getAttributeRow()
     * by store, so a store-scoped removal must be done explicitly here rather than through it.
     *
     * @param Category $category
     * @param string $linkField
     * @param string $attributeCode
     * @return void
     */
    private function removeStoreScopedAttributeOverride(
        Category $category,
        string $linkField,
        string $attributeCode
    ): void {
        $resource = $category->getResource();
        $attribute = $resource->getAttribute($attributeCode);
        $resource->getConnection()->delete(
            $attribute->getBackendTable(),
            [
                'attribute_id = ?' => $attribute->getAttributeId(),
                $linkField . ' = ?' => $category->getData($linkField),
                'store_id = ?' => (int) $category->getStoreId(),
            ]
        );
    }

    /**
     * Update Url Key
     *
     * @param Category $category
     * @param string|null $urlKey
     * @param bool $isStoreScopedRevert Whether this call is cascading a store-scoped "Use Default Value" revert
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function updateUrlKey(Category $category, ?string $urlKey, bool $isStoreScopedRevert = false): void
    {
        $this->validateUrlKey($category, $urlKey);
        $category->setUrlKey($urlKey)
            ->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
        if (!$category->isObjectNew()) {
            $category->getResource()->saveAttribute($category, 'url_path');
            if ($category->dataHasChangedFor('url_path')) {
                $this->updateUrlPathForChildren($category, $isStoreScopedRevert);
            }
        }
    }

    /**
     * Validate URL key value
     *
     * @param Category $category
     * @param string|null $urlKey
     * @return void
     * @throws LocalizedException
     */
    private function validateUrlKey(Category $category, ?string $urlKey): void
    {
        if (empty($urlKey) && !empty($category->getName()) && !empty($category->getUrlKey())) {
            throw new LocalizedException(
                __(
                    'Invalid URL key. The "%1" URL key can not be used to generate Latin URL key. ' .
                    'Please use Latin letters and numbers to avoid generating URL key issues.',
                    $category->getUrlKey()
                )
            );
        }

        if (empty($urlKey) && !empty($category->getName())) {
            throw new LocalizedException(
                __(
                    'Invalid URL key. The "%1" category name can not be used to generate Latin URL key. ' .
                    'Please add URL key or change category name using Latin letters and numbers to avoid generating ' .
                    'URL key issues.',
                    $category->getName()
                )
            );
        }

        if (empty($urlKey)) {
            throw new LocalizedException(__('Invalid URL key'));
        }

        $errors = $this->compositeUrlValidator->validate($urlKey);
        if (!empty($errors)) {
            throw new LocalizedException($errors[0]);
        }
    }

    /**
     * Update url path for children category.
     *
     * @param Category $category
     * @param bool $isStoreScopedRevert Whether this cascade is caused by a store-scoped "Use Default Value" revert
     * @return void
     * @throws NoSuchEntityException
     */
    protected function updateUrlPathForChildren(Category $category, bool $isStoreScopedRevert = false)
    {
        if ($this->isGlobalScope($category->getStoreId())) {
            $childrenIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
            foreach ($category->getStoreIds() as $storeId) {
                $this->updateOverriddenUrlPathForStore($category, $childrenIds, (int)$storeId);
            }
        } else {
            $children = $this->childrenCategoriesProvider->getChildren($category, true);
            $childrenById = [];
            foreach ($children as $child) {
                /** @var Category $child */
                $child->setStoreId($category->getStoreId());
                $childrenById[(int)$child->getId()] = $child;
            }
            uasort(
                $childrenById,
                static fn (Category $first, Category $second) => $first->getLevel() <=> $second->getLevel()
            );

            $storeId = (int)$category->getStoreId();
            // A revert only orphans children that never had their own url_key override; those with one
            // still need a recomputed url_path reflecting the new parent segment, not a deletion.
            $idsWithOwnUrlKeyOverride = [];
            $linkField = null;
            if ($isStoreScopedRevert && $childrenById) {
                $linkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
                $idsWithOwnUrlKeyOverride = $this->getIdsWithOwnUrlKeyOverride(
                    $category,
                    array_keys($childrenById),
                    $storeId
                );
            }

            $orphanedLinkIds = [];
            foreach ($childrenById as $childId => $child) {
                $parentId = (int)$child->getParentId();
                $parent = $parentId === (int)$category->getId() ? $category : ($childrenById[$parentId] ?? null);
                $isOrphanedOverride = $isStoreScopedRevert && !isset($idsWithOwnUrlKeyOverride[$childId]);
                $this->updateUrlPathForCategory($child, $parent, !$isOrphanedOverride);
                if ($isOrphanedOverride) {
                    $orphanedLinkIds[] = $child->getData($linkField);
                }
            }
            if ($orphanedLinkIds) {
                $this->deleteUrlPathOverridesForStore($category, $orphanedLinkIds, $storeId);
            }
        }
    }

    /**
     * Get, from the given category ids, those that carry their own store-scoped url_key override.
     *
     * @param Category $category
     * @param int[] $categoryIds
     * @param int $storeId
     * @return array<int, bool> Map of category id to true, for fast isset() lookups
     */
    private function getIdsWithOwnUrlKeyOverride(Category $category, array $categoryIds, int $storeId): array
    {
        $resource = $category->getResource();
        $attribute = $resource->getAttribute('url_key');
        $linkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
        $select = $resource->getConnection()->select()
            ->from($attribute->getBackendTable(), [$linkField])
            ->where('attribute_id = ?', $attribute->getAttributeId())
            ->where('store_id = ?', $storeId)
            ->where($linkField . ' IN (?)', $categoryIds);

        return array_flip($resource->getConnection()->fetchCol($select));
    }

    /**
     * Delete leftover store-scoped url_path overrides for children that no longer need one.
     *
     * @param Category $category
     * @param array $linkIds
     * @param int $storeId
     * @return void
     */
    private function deleteUrlPathOverridesForStore(Category $category, array $linkIds, int $storeId): void
    {
        $resource = $category->getResource();
        $attribute = $resource->getAttribute('url_path');
        $linkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
        $resource->getConnection()->delete(
            $attribute->getBackendTable(),
            [
                'attribute_id = ?' => $attribute->getAttributeId(),
                'store_id = ?' => $storeId,
                $linkField . ' IN (?)' => $linkIds,
            ]
        );
    }

    /**
     * Refresh overridden url_path values for a category and its descendants at a specific store scope.
     *
     * @param Category $category
     * @param int[] $childrenIds
     * @param int $storeId
     * @return void
     * @throws NoSuchEntityException
     */
    private function updateOverriddenUrlPathForStore(Category $category, array $childrenIds, int $storeId): void
    {
        $overriddenChildren = [];
        foreach ($childrenIds as $childId) {
            if ($this->storeViewService->doesEntityHaveOverriddenUrlPathForStore(
                $storeId,
                $childId,
                Category::ENTITY
            )) {
                $overriddenChildren[] = $this->categoryRepository->get($childId, $storeId);
            }
        }

        $categoryHasOverride = $this->storeViewService->doesEntityHaveOverriddenUrlPathForStore(
            $storeId,
            $category->getId(),
            Category::ENTITY
        );
        $needsStoreScopedParent = $categoryHasOverride || $this->hasDirectChild($overriddenChildren, $category);
        $storeScopedCategory = $needsStoreScopedParent ? $this->getStoreScopedCategory($category, $storeId) : null;

        if ($categoryHasOverride) {
            $this->updateUrlPathForCategory($storeScopedCategory);
        }

        usort(
            $overriddenChildren,
            static fn (Category $first, Category $second) => $first->getLevel() <=> $second->getLevel()
        );

        // Indexed by id so a descendant that is not a direct child can still be given its actual,
        // already-refreshed parent from this same pass, instead of falling through to a parentless
        // recompute that relies on a repository lookup which can return a stale cached instance.
        $overriddenChildrenById = [];
        foreach ($overriddenChildren as $child) {
            $overriddenChildrenById[(int)$child->getId()] = $child;
        }

        foreach ($overriddenChildren as $child) {
            $parentId = (int)$child->getParentId();
            if ($parentId === (int)$category->getId()) {
                $parent = $storeScopedCategory;
            } else {
                $parent = $overriddenChildrenById[$parentId] ?? null;
            }
            $this->updateUrlPathForCategory($child, $parent);
        }
    }

    /**
     * Check whether any of the given categories is a direct child of the edited category.
     *
     * @param Category[] $categories
     * @param Category $category
     * @return bool
     */
    private function hasDirectChild(array $categories, Category $category): bool
    {
        foreach ($categories as $candidate) {
            if ((int)$candidate->getParentId() === (int)$category->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Re-scope the edited category to a specific store, preserving its just-changed url_key.
     *
     * @param Category $category
     * @param int $storeId
     * @return Category
     * @throws NoSuchEntityException
     */
    private function getStoreScopedCategory(Category $category, int $storeId): Category
    {
        $isEditedScope = $storeId === (int)$category->getStoreId();
        if (!$isEditedScope && $this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(
            $storeId,
            $category->getId(),
            Category::ENTITY
        )) {
            return $this->categoryRepository->get($category->getId(), $storeId);
        }

        $storeScopedCategory = clone $category;
        $storeScopedCategory->setStoreId($storeId);
        return $storeScopedCategory;
    }

    /**
     * Check is global scope
     *
     * @param int|null $storeId
     * @return bool
     */
    protected function isGlobalScope($storeId)
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }

    /**
     * Update url path for category.
     *
     * The recomputed path is still needed even when not persisted, so a descendant further down the
     * cascade that does have its own override can still thread an accurate parent path through.
     *
     * @param Category $category
     * @param Category|null $parentCategory
     * @param bool $shouldPersist
     * @return void
     * @throws NoSuchEntityException
     */
    protected function updateUrlPathForCategory(
        Category $category,
        ?Category $parentCategory = null,
        bool $shouldPersist = true
    ) {
        $category->unsUrlPath();
        $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category, $parentCategory));
        if ($shouldPersist) {
            $category->getResource()->saveAttribute($category, 'url_path');
        }
    }
}
