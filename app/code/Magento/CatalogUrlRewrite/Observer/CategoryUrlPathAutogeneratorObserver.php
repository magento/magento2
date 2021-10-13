<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\GetDefaultUrlKey;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;

/**
 * Class for set or update url path.
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
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param StoreViewService $storeViewService
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CompositeUrlKey $compositeUrlValidator
     * @param GetDefaultUrlKey $getDefaultUrlKey
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        StoreViewService $storeViewService,
        CategoryRepositoryInterface $categoryRepository,
        CompositeUrlKey $compositeUrlValidator,
        GetDefaultUrlKey $getDefaultUrlKey
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->storeViewService = $storeViewService;
        $this->categoryRepository = $categoryRepository;
        $this->compositeUrlValidator = $compositeUrlValidator;
        $this->getDefaultUrlKey = $getDefaultUrlKey;
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
            if ($category->hasChildren()) {
                $defaultUrlKey = $this->getDefaultUrlKey->execute((int)$category->getId());
                if ($defaultUrlKey) {
                    $this->updateUrlKey($category, $defaultUrlKey);
                }
            }
            $category->setUrlKey(null)->setUrlPath(null);
        }
    }

    /**
     * Update Url Key
     *
     * @param Category $category
     * @param string|null $urlKey
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function updateUrlKey(Category $category, ?string $urlKey): void
    {
        $this->validateUrlKey($category, $urlKey);
        $category->setUrlKey($urlKey)
            ->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
        if (!$category->isObjectNew()) {
            $category->getResource()->saveAttribute($category, 'url_path');
            if ($category->dataHasChangedFor('url_path')) {
                $this->updateUrlPathForChildren($category);
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
     * @return void
     */
    protected function updateUrlPathForChildren(Category $category)
    {
        if ($this->isGlobalScope($category->getStoreId())) {
            $childrenIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
            foreach ($childrenIds as $childId) {
                foreach ($category->getStoreIds() as $storeId) {
                    if ($this->storeViewService->doesEntityHaveOverriddenUrlPathForStore(
                        $storeId,
                        $childId,
                        Category::ENTITY
                    )) {
                        $child = $this->categoryRepository->get($childId, $storeId);
                        $this->updateUrlPathForCategory($child);
                    }
                }
            }
        } else {
            $children = $this->childrenCategoriesProvider->getChildren($category, true);
            foreach ($children as $child) {
                /** @var Category $child */
                $child->setStoreId($category->getStoreId());
                if ($child->getParentId() === $category->getId()) {
                    $this->updateUrlPathForCategory($child, $category);
                } else {
                    $this->updateUrlPathForCategory($child);
                }
            }
        }
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
     * @param Category $category
     * @param Category|null $parentCategory
     * @return void
     * @throws NoSuchEntityException
     */
    protected function updateUrlPathForCategory(Category $category, Category $parentCategory = null)
    {
        $category->unsUrlPath();
        $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category, $parentCategory));
        $category->getResource()->saveAttribute($category, 'url_path');
    }
}
