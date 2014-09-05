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
namespace Magento\Catalog\Service\V1\Data\Eav\Category;

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;

/**
 * @codeCoverageIgnore
 */
class TreeBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * Set category ID
     *
     * @param int $categoryId
     * @return $this
     */
    public function setId($categoryId)
    {
        return $this->_set(Tree::ID, $categoryId);
    }

    /**
     * Set parent category ID
     *
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId)
    {
        return $this->_set(Tree::PARENT_ID, $parentId);
    }

    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->_set(Tree::NAME, $name);
    }

    /**
     * Set whether category is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setActive($isActive)
    {
        return $this->_set(Tree::ACTIVE, $isActive);
    }

    /**
     * Set category position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->_set(Tree::POSITION, $position);
    }

    /**
     * Set product count
     *
     * @param int $productCount
     * @return int
     */
    public function setProductCount($productCount)
    {
        return $this->_set(Tree::PRODUCT_COUNT, $productCount);
    }

    /**
     * Set category level
     *
     * @param int $level
     * @return $this
     */
    public function setLevel($level)
    {
        return $this->_set(Tree::LEVEL, $level);
    }

    /**
     * Set category level
     *
     * @param \Magento\Catalog\Service\V1\Data\Eav\Category\Tree[] $children
     * @return $this
     */
    public function setChildren(array $children)
    {
        return $this->_set(Tree::CHILDREN, $children);
    }
}
