<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Catalog\Model\Category;

class ChildrenCategoriesProvider
{
    /** @var array */
    protected $childrenIds = [];

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param boolean $recursive
     * @return \Magento\Catalog\Model\Category[]
     */
    public function getChildren(Category $category, $recursive = false)
    {
        return $category->isObjectNew() ? [] : $category->getResourceCollection()
            ->addAttributeToSelect('url_path')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addIdFilter($this->getChildrenIds($category, $recursive));
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param boolean $recursive
     * @return int[]
     */
    public function getChildrenIds(Category $category, $recursive = false)
    {
        $cacheKey = $category->getId() . '_' . (int)$recursive;
        if (!isset($this->childrenIds[$cacheKey])) {
            $connection = $category->getResource()->getReadConnection();
            $select = $connection->select()
                ->from($category->getResource()->getEntityTable(), 'entity_id')
                ->where($connection->quoteIdentifier('path') . ' LIKE :c_path');
            $bind = ['c_path' => $category->getPath() . '/%'];
            if (!$recursive) {
                $select->where($connection->quoteIdentifier('level') . ' <= :c_level');
                $bind['c_level'] = $category->getLevel() + 1;
            }
            $this->childrenIds[$cacheKey] = $connection->fetchCol($select, $bind);
        }
        return $this->childrenIds[$cacheKey];
    }
}
