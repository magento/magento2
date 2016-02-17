<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Catalog\Api\Data\CategoryInterface;

class CategoryRepository implements \Magento\Catalog\Api\CategoryRepositoryInterface
{
    /**
     * @var Category[]
     */
    protected $instances = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResource;

    /**
     * @var \Magento\Framework\Model\Entity\MetadataPool
     */
    protected $metadataPool;

    /**
     * List of fields that can used config values in case when value does not defined directly
     *
     * @var array
     */
    protected $useConfigFields = ['available_sort_by', 'default_sort_by', 'filter_price_range'];

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Framework\Model\Entity\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Entity\MetadataPool $metadataPool,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\CategoryInterface $category)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $existingData = $this->extensibleDataObjectConverter
            ->toNestedArray($category, [], 'Magento\Catalog\Api\Data\CategoryInterface');
        $existingData = array_diff_key($existingData, array_flip(['path', 'level', 'parent_id']));
        $existingData['store_id'] = $storeId;

        if ($category->getId()) {
            $metadata = $this->metadataPool->getMetadata(
                CategoryInterface::class
            );

            $category = $this->get($category->getId(), $storeId);
            $existingData[$metadata->getLinkField()] = $category->getData(
                $metadata->getLinkField()
            );

            if (isset($existingData['image']) && is_array($existingData['image'])) {
                $existingData['image_additional_data'] = $existingData['image'];
                unset($existingData['image']);
            }
        } else {
            $parentId = $category->getParentId() ?: $this->storeManager->getStore()->getRootCategoryId();
            $parentCategory = $this->get($parentId, $storeId);
            $existingData['path'] = $parentCategory->getPath();
            $existingData['parent_id'] = $parentId;
        }
        $category->addData($existingData);
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
     * {@inheritdoc}
     */
    public function get($categoryId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
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
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\CategoryInterface $category)
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
     * {@inheritdoc}
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
                        __('Attribute "%1" is required.', $attribute)
                    );
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__($error));
                }
            }
        }
        $category->unsetData('use_post_data_config');
    }
}
