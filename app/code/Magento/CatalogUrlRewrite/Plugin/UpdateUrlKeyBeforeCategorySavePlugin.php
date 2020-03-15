<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Service\CategoryUrlPathUpdateService;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * Plugin handles `url_key` and `url_path` before saving Category
 */
class UpdateUrlKeyBeforeCategorySavePlugin
{
    /**
     * @var CategoryUrlPathGenerator
     */
    private $urlPathGenerator;

    /**
     * @var FrontNameResolver
     */
    private $frontNameResolver;

    /**
     * @var CategoryUrlPathUpdateService
     */
    private $urlPathUpdateService;

    /**
     * @var string[]
     */
    private $invalidValues;

    /**
     * @param CategoryUrlPathGenerator $urlPathGenerator
     * @param FrontNameResolver $frontNameResolver
     * @param CategoryUrlPathUpdateService $urlPathUpdateService
     * @param array $invalidValues
     */
    public function __construct(
        CategoryUrlPathGenerator $urlPathGenerator,
        FrontNameResolver $frontNameResolver,
        CategoryUrlPathUpdateService $urlPathUpdateService,
        array $invalidValues = []
    ) {
        $this->urlPathGenerator = $urlPathGenerator;
        $this->frontNameResolver = $frontNameResolver;
        $this->urlPathUpdateService = $urlPathUpdateService;
        $this->invalidValues = $invalidValues;
    }

    /**
     * Executed before `save` on ResourceModel to update `url_key` and `url_path`
     *
     * @param CategoryResourceModel $resourceModel
     * @param DataObject $category
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeSave(CategoryResourceModel $resourceModel, DataObject $category)
    {
        /** @var Category $category */

        $generatedUrlKey = $this->urlPathGenerator->getUrlKey($category);

        if ($this->isUseDefaultUrlKey($category)) {
            if (!$category->isObjectNew() && $this->isCategoryGlobal($category)) {
                $this->setUrlKey($category, $generatedUrlKey);
                $this->updateUrlKey($resourceModel, $category);
                return;
            }

            $category->setUrlKey(null)->setUrlPath(null);
            return;
        }

        if ($category->getUrlKey() !== false) {
            $this->setUrlKey($category, $generatedUrlKey);
            $this->updateUrlKey($resourceModel, $category);
        }
    }

    /**
     * Determines whether `use_default` contains `url_key` key
     *
     * @param Category $category
     * @return bool
     */
    private function isUseDefaultUrlKey(Category $category): bool
    {
        $useDefaultAttributes = $category->getData('use_default');
        return isset($useDefaultAttributes['url_key']) && $useDefaultAttributes['url_key'];
    }

    /**
     * Determines whether Category should be saved in Global scope
     *
     * @param Category $category
     * @return bool
     */
    private function isCategoryGlobal(Category $category): bool
    {
        $storeId = $category->getStoreId();

        // Unfortunately Magento still likes to return `string` as `store_id`.
        return null === $storeId || (string)Store::DEFAULT_STORE_ID === (string)$storeId;
    }

    /**
     * Updates `url_key` and as a result `url_path` for Category
     *
     * @param Category $category
     * @param string $urlKey
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function setUrlKey(Category $category, string $urlKey): void
    {
        $this->validateUrlKey($urlKey);

        $category->setUrlKey($urlKey);
        $category->setUrlPath($this->urlPathGenerator->getUrlPath($category));
    }

    /**
     * Performs DB call to update `url_path` value
     *
     * @param CategoryResourceModel $resourceModel
     * @param Category $category
     * @throws NoSuchEntityException
     */
    private function updateUrlKey(CategoryResourceModel $resourceModel, Category $category): void
    {
        if ($category->isObjectNew()) {
            return;
        }

        $resourceModel->saveAttribute($category, 'url_path');
        if ($category->dataHasChangedFor('url_path')) {
            $this->urlPathUpdateService->execute($category);
        }
    }

    /**
     * Verifies `url_key` agains several conditions
     *
     * @param string $urlKey
     * @throws LocalizedException
     */
    private function validateUrlKey(string $urlKey)
    {
        if (empty($urlKey)) {
            throw new LocalizedException(__('Invalid URL key'));
        }

        if (in_array($urlKey, $this->getReservedKeys())) {
            throw new LocalizedException(__(
                'URL key "%1" matches a reserved endpoint name (%2). Use another URL key.',
                $urlKey,
                implode(', ', $this->getReservedKeys())
            ));
        }
    }

    /**
     * Returns list of reserved keys that are injected via DI
     *
     * @return string[]
     */
    private function getReservedKeys()
    {
        $reservedKeys = array_merge($this->invalidValues, [$this->frontNameResolver->getFrontName()]);

        return array_unique($reservedKeys);
    }
}
