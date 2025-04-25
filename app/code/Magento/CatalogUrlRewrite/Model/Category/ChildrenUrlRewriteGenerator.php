<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Model for generate url rewrites for children categories
 */
class ChildrenUrlRewriteGenerator
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider
     */
    protected $childrenCategoriesProvider;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory
     */
    protected $categoryUrlRewriteGeneratorFactory;

    /**
     * @var \Magento\UrlRewrite\Model\MergeDataProvider
     */
    private $mergeDataProviderPrototype;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory $categoryUrlRewriteGeneratorFactory
     * @param \Magento\UrlRewrite\Model\MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param CategoryRepositoryInterface|null $categoryRepository
     */
    public function __construct(
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        CategoryUrlRewriteGeneratorFactory $categoryUrlRewriteGeneratorFactory,
        ?MergeDataProviderFactory $mergeDataProviderFactory = null,
        ?CategoryRepositoryInterface $categoryRepository = null
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGeneratorFactory = $categoryUrlRewriteGeneratorFactory;
        if (!isset($mergeDataProviderFactory)) {
            $mergeDataProviderFactory = ObjectManager::getInstance()->get(MergeDataProviderFactory::class);
        }
        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
        $this->categoryRepository = $categoryRepository
            ?: ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
    }

    /**
     * Generate list of children urls
     *
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category $category
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generate($storeId, Category $category, $rootCategoryId = null)
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $childrenIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
        if ($childrenIds) {
            foreach ($childrenIds as $childId) {
                /** @var Category $childCategory */
                $childCategory = $this->categoryRepository->get($childId, $storeId);
                $childCategory->setData('save_rewrites_history', $category->getData('save_rewrites_history'));
                /** @var CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator */
                $categoryUrlRewriteGenerator = $this->categoryUrlRewriteGeneratorFactory->create();
                $mergeDataProvider->merge(
                    $categoryUrlRewriteGenerator->generate($childCategory, false, $rootCategoryId)
                );
            }
        }

        return $mergeDataProvider->getData();
    }
}
