<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Category;

/**
 * Map that holds data for category ids and it's subcategories ids
 */
class DataCategoryMap implements DataMapInterface
{
    /** @var array */
    private $data = [];

    /** @var CategoryFactory */
    private $categoryFactory;

    /**
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Gets all data from a map identified by a category Id
     *
     * @param int $categoryId
     * @return array
     */
    public function getData($categoryId)
    {
        if (empty($this->data[$categoryId])) {
            $this->data[$categoryId] = $this->queryData($categoryId);
        }
        return $this->data[$categoryId];
    }

    /**
     * Queries the database and returns results
     *
     * @param int $categoryId
     * @return array
     */
    private function queryData($categoryId)
    {
        $category = $this->categoryFactory->create()->load($categoryId);
        return $category->getResourceCollection()
            ->addIdFilter($this->getAllCategoryChildrenIds($category))
            ->getAllIds();
    }

    /**
     * Queries sub-categories ids from a category
     *
     * @param Category $category
     * @return int[]
     */
    private function getAllCategoryChildrenIds($category)
    {
        $connection = $category->getResource()->getConnection();
        $select = $connection->select()
            ->from($category->getResource()->getEntityTable(), 'entity_id')
            ->where($connection->quoteIdentifier('path') . ' LIKE :c_path');
        $bind = ['c_path' => $category->getPath() . '%'];
        return $connection->fetchCol($select, $bind);
    }

    /**
     * Resets current map
     *
     * @param int $categoryId
     * @return $this
     */
    public function resetData($categoryId)
    {
        unset($this->data);
        $this->data = [];
        return $this;
    }
}
