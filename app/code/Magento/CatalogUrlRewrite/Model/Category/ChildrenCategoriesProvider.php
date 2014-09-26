<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Catalog\Model\Category;

class ChildrenCategoriesProvider
{
    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param boolean $recursive
     * @return \Magento\Catalog\Model\Category[]
     */
    public function getChildren(Category $category, $recursive = false)
    {
        return $category->getResourceCollection()
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
        $connection = $category->getResource()->getReadConnection();
        $select = $connection->select()
            ->from($category->getResource()->getEntityTable(), 'entity_id')
            ->where($connection->quoteIdentifier('path') . ' LIKE :c_path');
        $bind = ['c_path' => $category->getPath() . '/%'];
        if (!$recursive) {
            $select->where($connection->quoteIdentifier('level') . ' <= :c_level');
            $bind['c_level'] = $category->getLevel() + 1;
        }

        return $connection->fetchCol($select, $bind);
    }
}
