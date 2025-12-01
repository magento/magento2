<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository\PopulateWithValues;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Repository for categories.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepository implements CategoryRepositoryInterface, ResetAfterRequestInterface
{
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
     * @var CategoryResource
     */
    protected $categoryResource;

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
     * @var array
     */
    protected $useConfigFields = ['available_sort_by', 'default_sort_by', 'filter_price_range'];

    /**
     * @var PopulateWithValues
     */
    private $populateWithValues;

    /**
     * @param CategoryFactory $categoryFactory
     * @param CategoryResource $categoryResource
     * @param StoreManagerInterface $storeManager
     * @param PopulateWithValues|null $populateWithValues
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        StoreManagerInterface $storeManager,
        ?PopulateWithValues $populateWithValues
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
        $objectManager = ObjectManager::getInstance();
        $this->populateWithValues = $populateWithValues ?? $objectManager->get(PopulateWithValues::class);
    }

    /**
     * @inheritdoc
     */
    public function save(CategoryInterface $category)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $existingData = $this->getExtensibleDataObjectConverter()
            ->toNestedArray($category, [], CategoryInterface::class);
        $existingData = array_diff_key($existingData, array_flip(['path', 'level', 'parent_id']));
        $existingData['store_id'] = $storeId;

        if ($category->getId()) {
            $metadata = $this->getMetadataPool()->getMetadata(
                CategoryInterface::class
            );

            $category = $this->get($category->getId(), $storeId);
            $existingData[$metadata->getLinkField()] = $category->getData(
                $metadata->getLinkField()
            );

            if (isset($existingData['image']) && is_array($existingData['image'])) {
                if (!empty($existingData['image']['delete'])) {
                    $existingData['image'] = null;
                } else {
                    if (isset($existingData['image'][0]['name']) && isset($existingData['image'][0]['tmp_name'])) {
                        $existingData['image'] = $existingData['image'][0]['name'];
                    } else {
                        unset($existingData['image']);
                    }
                }
            }
        } else {
            $parentId = $category->getParentId() ?: $this->storeManager->getStore()->getRootCategoryId();
            $parentCategory = $this->get($parentId, $storeId);
            $existingData['path'] = $parentCategory->getPath();
            $existingData['parent_id'] = $parentId;
            $existingData['level'] = null;
        }
        $this->populateWithValues->execute($category, $existingData);
        try {
            $this->validateCategory($category);
            $this->categoryResource->save($category);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save category: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
        unset($this->instances[$category->getId()]);
        return $this->get($category->getId(), $storeId);
    }

    /**
     * @inheritdoc
     */
    public function get($categoryId, $storeId = null)
    {
        $cacheKey = $storeId ?? $this->storeManager->getStore()->getId();
        if (!isset($this->instances[$categoryId][$cacheKey])) {
            /** @var Category $category */
            $category = $this->categoryFactory->create();
            if (null !== $storeId) {
                $category->setStoreId($storeId);
            }
            $category->load($categoryId);
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
            $this->categoryResource->delete($category);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete category with id %1',
                    $category->getId()
                ),
                $e
            );
        }
        unset($this->instances[$categoryId]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteByIdentifier($categoryId)
    {
        $category = $this->get($categoryId);
        return  $this->delete($category);
    }

    /**
     * Validate category process
     *
     * @param  Category $category
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
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
                    $attribute = $this->categoryResource->getAttribute($code)->getFrontend()->getLabel();
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The "%1" attribute is required. Enter and try again.', $attribute)
                    );
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__($error));
                }
            }
        }
        $category->unsetData('use_post_data_config');
    }

    /**
     * Lazy loader for the converter.
     *
     * @return ExtensibleDataObjectConverter
     * phpcs:disable Magento2.Annotation.MethodAnnotationStructure
     * @deprecated 101.0.0
     * @see we don't recommend this approach anymore
     */
    private function getExtensibleDataObjectConverter()
    {
        if ($this->extensibleDataObjectConverter === null) {
            $this->extensibleDataObjectConverter = ObjectManager::getInstance()
                ->get(ExtensibleDataObjectConverter::class);
        }
        return $this->extensibleDataObjectConverter;
    }

    /**
     * Lazy loader for the metadata pool.
     *
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()
                ->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->instances = [];
    }
}
