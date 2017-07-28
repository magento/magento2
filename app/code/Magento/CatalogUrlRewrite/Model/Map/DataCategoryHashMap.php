<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Catalog\Model\ResourceModel\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * Map that holds data for category ids and its subcategories ids
 * @since 2.2.0
 */
class DataCategoryHashMap implements HashMapInterface
{
    /**
     * @var int[]
     * @since 2.2.0
     */
    private $hashMap = [];

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     * @since 2.2.0
     */
    private $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\CategoryFactory
     * @since 2.2.0
     */
    private $categoryResourceFactory;

    /**
     * @param CategoryRepository $categoryRepository
     * @param CategoryFactory $categoryResourceFactory
     * @since 2.2.0
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        CategoryFactory $categoryResourceFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryResourceFactory = $categoryResourceFactory;
    }

    /**
     * Returns an array of categories ids that includes category identified by $categoryId and all its subcategories
     *
     * @param int $categoryId
     * @return array
     * @since 2.2.0
     */
    public function getAllData($categoryId)
    {
        if (!isset($this->hashMap[$categoryId])) {
            $category = $this->categoryRepository->get($categoryId);
            $this->hashMap[$categoryId] = $this->getAllCategoryChildrenIds($category);
        }
        return $this->hashMap[$categoryId];
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getData($categoryId, $key)
    {
        $categorySpecificData = $this->getAllData($categoryId);
        if (isset($categorySpecificData[$key])) {
            return $categorySpecificData[$key];
        }
        return [];
    }

    /**
     * Queries the database for sub-categories ids from a category
     *
     * @param CategoryInterface $category
     * @return int[]
     * @since 2.2.0
     */
    private function getAllCategoryChildrenIds(CategoryInterface $category)
    {
        $categoryResource = $this->categoryResourceFactory->create();
        $connection = $categoryResource->getConnection();
        $select = $connection->select()
            ->from($categoryResource->getEntityTable(), 'entity_id')
            ->where($connection->quoteIdentifier('path') . ' LIKE :c_path');
        $bind = ['c_path' => $category->getPath() . '%'];
        return $connection->fetchCol($select, $bind);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function resetData($categoryId)
    {
        unset($this->hashMap[$categoryId]);
    }
}
