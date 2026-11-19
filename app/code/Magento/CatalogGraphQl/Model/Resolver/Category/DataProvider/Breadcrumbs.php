<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Breadcrumbs data provider
 */
class Breadcrumbs implements ResetAfterRequestInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Breadcrumb items per store and category id; false marks an id the collection did not return
     *
     * @var array<int, array<int, array|false>>
     */
    private array $breadcrumbItems = [];

    /**
     * @param CollectionFactory $collectionFactory
     * @param Uid|null $uidEncoder
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ?Uid $uidEncoder = null,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->uidEncoder = $uidEncoder ?: ObjectManager::getInstance()
            ->get(Uid::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
    }

    /**
     * Get breadcrumbs data
     *
     * @param string $categoryPath
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData(string $categoryPath): array
    {
        $parentCategoryIds = $this->getParentCategoryIds($categoryPath);
        if (!count($parentCategoryIds)) {
            return [];
        }

        $storeId = (int)$this->storeManager->getStore()->getId();
        $this->loadBreadcrumbItems($parentCategoryIds, $storeId);
        // Same order as the per-path collection without ORDER BY returned: primary key order
        sort($parentCategoryIds, SORT_NUMERIC);
        $breadcrumbsData = [];
        foreach ($parentCategoryIds as $categoryId) {
            $item = $this->breadcrumbItems[$storeId][(int)$categoryId] ?? false;
            if ($item !== false) {
                $breadcrumbsData[] = $item;
            }
        }

        return $breadcrumbsData;
    }

    /**
     * Load the parent categories of several category paths at once, so getData() serves them without a query
     *
     * @param string[] $categoryPaths
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prefetch(array $categoryPaths): void
    {
        $categoryIds = [];
        foreach ($categoryPaths as $categoryPath) {
            $categoryIds[] = $this->getParentCategoryIds((string)$categoryPath);
        }
        $categoryIds = array_merge([], ...$categoryIds);
        if (count($categoryIds)) {
            $this->loadBreadcrumbItems($categoryIds, (int)$this->storeManager->getStore()->getId());
        }
    }

    /**
     * Get ids of the parent categories of a category path, without the root categories
     *
     * @param string $categoryPath
     * @return string[]
     */
    private function getParentCategoryIds(string $categoryPath): array
    {
        return array_slice(explode('/', $categoryPath), 2, -1);
    }

    /**
     * Load breadcrumb items of the categories not loaded for the store yet
     *
     * @param array $categoryIds
     * @param int $storeId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function loadBreadcrumbItems(array $categoryIds, int $storeId): void
    {
        $idsToLoad = array_values(
            array_diff(array_unique($categoryIds), array_keys($this->breadcrumbItems[$storeId] ?? []))
        );
        if (!$idsToLoad) {
            return;
        }

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['name', 'url_key', 'url_path']);
        $collection->addAttributeToFilter('entity_id', $idsToLoad);
        $collection->addAttributeToFilter(CategoryInterface::KEY_IS_ACTIVE, 1);

        foreach ($collection as $category) {
            $this->breadcrumbItems[$storeId][(int)$category->getId()] = [
                'category_id' => $category->getId(),
                'category_uid' => $this->uidEncoder->encode((string) $category->getId()),
                'category_name' => $category->getName(),
                'category_level' => $category->getLevel(),
                'category_url_key' => $category->getUrlKey(),
                'category_url_path' => $category->getUrlPath(),
            ];
        }
        foreach ($idsToLoad as $categoryId) {
            $this->breadcrumbItems[$storeId][(int)$categoryId] ??= false;
        }
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->breadcrumbItems = [];
    }
}
