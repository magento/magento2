<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class CategoryRepository implements \Magento\Catalog\Api\CategoryRepositoryInterface
{
    /**
     * @var Category[]
     * @since 2.0.0
     */
    protected $instances = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     * @since 2.0.0
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     * @since 2.0.0
     */
    protected $categoryResource;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     * @since 2.1.0
     */
    private $extensibleDataObjectConverter;

    /**
     * List of fields that can used config values in case when value does not defined directly
     *
     * @var array
     * @since 2.0.0
     */
    protected $useConfigFields = ['available_sort_by', 'default_sort_by', 'filter_price_range'];

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(\Magento\Catalog\Api\Data\CategoryInterface $category)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $existingData = $this->getExtensibleDataObjectConverter()
            ->toNestedArray($category, [], \Magento\Catalog\Api\Data\CategoryInterface::class);
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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

    /**
     * @return \Magento\Framework\Api\ExtensibleDataObjectConverter
     *
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getExtensibleDataObjectConverter()
    {
        if ($this->extensibleDataObjectConverter === null) {
            $this->extensibleDataObjectConverter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\ExtensibleDataObjectConverter::class);
        }
        return $this->extensibleDataObjectConverter;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     * @since 2.1.0
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
