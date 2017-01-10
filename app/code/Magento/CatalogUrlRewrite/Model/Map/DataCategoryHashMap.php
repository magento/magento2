<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * Map that holds data for category ids and its subcategories ids
 */
class DataCategoryHashMap implements HashMapInterface
{
    /** @var int[] */
    private $hashMap = [];

    /** @var CategoryRepository */
    private $categoryRepository;

    /** @var Collection */
    private $collection;

    /** @var Category */
    private $categoryResource;

    /**
     * @param CategoryRepository $categoryRepository
     * @param Collection $collection
     * @param Category $categoryResource
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        Collection $collection,
        Category $categoryResource
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->collection = $collection;
        $this->categoryResource = $categoryResource;
    }

    /**
     * Returns an array of categories ids that includes category identified by $categoryId and all its subcategories
     *
     * @param int $categoryId
     * @return array
     */
    public function getAllData($categoryId)
    {
        if (!isset($this->hashMap[$categoryId])) {
            $category = $this->categoryRepository->get($categoryId);
            $this->hashMap[$categoryId] = $this->collection->addIdFilter($this->getAllCategoryChildrenIds($category))
                ->getAllIds();
        }
        return $this->hashMap[$categoryId];
    }

    /**
     * {@inheritdoc}
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
     */
    private function getAllCategoryChildrenIds(CategoryInterface $category)
    {
        $connection = $this->categoryResource->getConnection();
        $select = $connection->select()
            ->from($this->categoryResource->getEntityTable(), 'entity_id')
            ->where($connection->quoteIdentifier('path') . ' LIKE :c_path');
        $bind = ['c_path' => $category->getPath() . '%'];
        return $connection->fetchCol($select, $bind);
    }

    /**
     * {@inheritdoc}
     */
    public function resetData($categoryId)
    {
        unset($this->hashMap[$categoryId]);
    }
}
