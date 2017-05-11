<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManager;

/**
 * Provide category id. Find category in default store group by specified website and category name or create new one
 */
class CategoryResolver
{
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $categories = [];

    /**
     * @param StoreManager $storeManager
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CollectionFactory $collectionFactory
     * @internal param Category $category
     */
    public function __construct(
        StoreManager $storeManager,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $collectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->collectionFactory = $collectionFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get category id
     *
     * @param int $websiteId
     * @param string $categoryName
     * @return int
     */
    public function getCategory($websiteId, $categoryName)
    {
        $categoryKey = $websiteId . $categoryName;

        if (!isset($this->categories[$categoryKey])) {
            $website = $this->storeManager->getWebsite($websiteId);
            $rootCategoryId = $website->getDefaultGroup()->getRootCategoryId();
            $website->getDefaultGroup()->getStoreId();
            $category = $this->collectionFactory->create()
                ->addFieldToFilter('parent_id', $rootCategoryId)
                ->addFieldToFilter('name', $categoryName)
                ->fetchItem();
            if ($category && $category->getId()) {
                $this->categories[$categoryKey] = $category->getId();
            } else {
                $category = $this->categoryFactory->create(
                    [
                        'data' => [
                            'parent_id' => $rootCategoryId,
                            'name' => $categoryName,
                            'position' => 1,
                            'is_active' => true,
                            'available_sort_by' => ['position', 'name'],
                            'url_key' => $categoryName . '-' . $websiteId
                        ]
                    ]
                );
                $category = $this->categoryRepository->save($category);
                $this->categories[$categoryKey] = $category->getId();
            }
        }

        return $this->categories[$categoryKey];
    }
}
