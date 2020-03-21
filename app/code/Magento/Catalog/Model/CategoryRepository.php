<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Repository for categories.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    private const CACHE_TAG_ALL_STORES = 'all';

    /**
     * @var Category[]
     */
    protected $instances = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CategoryResourceModel
     */
    protected $categoryResourceModel;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * List of fields that can used config values in case when value does not defined directly
     *
     * @var string[]
     */
    protected $useConfigFields = ['available_sort_by', 'default_sort_by', 'filter_price_range'];

    /**
     * @param CategoryFactory $categoryFactory
     * @param CategoryResourceModel $categoryResourceModel
     * @param StoreManagerInterface $storeManager
     * @param ExtensibleDataObjectConverter|null $extensibleDataObjectConverter
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryResourceModel $categoryResourceModel,
        StoreManagerInterface $storeManager,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter = null,
        MetadataPool $metadataPool = null
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->storeManager = $storeManager;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter
            ?? ObjectManager::getInstance()->get(ExtensibleDataObjectConverter::class);
        $this->metadataPool = $metadataPool ?? ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * @inheritdoc
     */
    public function save(CategoryInterface $category)
    {
        $storeId = $this->getCategoryStoreId($category);

        $existingData = $this->extensibleDataObjectConverter->toNestedArray($category, [], CategoryInterface::class);
        $existingData = array_diff_key($existingData, array_flip(['path', 'level', 'parent_id']));
        $existingData['store_id'] = $storeId;

        if ($category->getId()) {
            $metadata = $this->metadataPool->getMetadata(CategoryInterface::class);

            $category = $this->get($category->getId(), $storeId);
            $existingData[$metadata->getLinkField()] = $category->getData($metadata->getLinkField());
            $existingData = $this->handleCategoryImage($existingData);
        } else {
            $parentId = $category->getParentId() ?: $this->storeManager->getStore()->getRootCategoryId();
            $parentCategory = $this->get($parentId, $storeId);
            $existingData['path'] = $parentCategory->getPath();
            $existingData['parent_id'] = $parentId;
            $existingData['level'] = null;
        }
        $category->addData($existingData);

        try {
            $this->validateCategory($category);
            $this->categoryResourceModel->save($category);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save category: %1', $e->getMessage()),
                $e
            );
        }
        $this->flushCategoryCache($category->getId());
        return $this->get($category->getId(), $storeId);
    }

    /**
     * @inheritdoc
     */
    public function get($categoryId, $storeId = null)
    {
        $cacheKey = $storeId ?? self::CACHE_TAG_ALL_STORES;

        if (!isset($this->instances[$categoryId][$cacheKey])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();
            if (null !== $storeId) {
                $category->setStoreId($storeId);
            }

            $this->categoryResourceModel->load($category, $categoryId);

            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('id', $categoryId);
            }
            $this->instances[$categoryId][$cacheKey] = $category;
        }

        return $this->instances[$categoryId][$cacheKey];
    }

    /**
     * @inheritdoc
     */
    public function delete(CategoryInterface $category)
    {
        try {
            $categoryId = $category->getId();
            $this->categoryResourceModel->delete($category);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot delete category with id %1', $category->getId()), $e);
        }

        $this->flushCategoryCache($categoryId);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteByIdentifier($categoryId)
    {
        $category = $this->get($categoryId);
        return $this->delete($category);
    }

    /**
     * Validate category process
     *
     * @param Category $category
     * @return void
     * @throws LocalizedException
     */
    protected function validateCategory(Category $category)
    {
        $useConfigFields = [];
        foreach ($this->useConfigFields as $field) {
            if (!$category->getData($field)) {
                $useConfigFields[] = $field;
            }
        }
        $category->setData('use_post_data_config', $useConfigFields);
        $validate = $category->validate();
        if ($validate !== true) {
            foreach ($validate as $code => $error) {
                if ($error === true) {
                    $attribute = $this->categoryResourceModel->getAttribute($code)->getFrontend()->getLabel();
                    throw new LocalizedException(
                        __('The "%1" attribute is required. Enter and try again.', $attribute)
                    );
                } else {
                    throw new LocalizedException(__($error));
                }
            }
        }
        $category->unsetData('use_post_data_config');
    }

    /**
     * If CategoryInterface object has `store_id` set, use it during save. Otherwise use Current `store_id`.
     *
     * @param CategoryInterface $category
     * @return int
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getCategoryStoreId(CategoryInterface $category): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Method removes object by ID from Repository internal cache.
     *
     * @param int $categoryId
     * @see \Magento\Catalog\Model\CategoryRepository::$instances
     */
    private function flushCategoryCache(int $categoryId): void
    {
        unset($this->instances[$categoryId]);
    }

    /**
     * Determines whether Delete or Update action was requested. Performs necessary actions.
     *
     * @param array $categoryData
     * @return array
     */
    private function handleCategoryImage(array $categoryData): array
    {
        if (isset($categoryData['image']) && is_array($categoryData['image'])) {
            if (isset($categoryData['image']['delete']) && !empty($categoryData['image']['delete'])) {
                $categoryData['image'] = null;
            } else {
                if (isset($categoryData['image'][0]['name']) && isset($categoryData['image'][0]['tmp_name'])) {
                    $categoryData['image'] = $categoryData['image'][0]['name'];
                } else {
                    unset($categoryData['image']);
                }
            }
        }

        return $categoryData;
    }
}
