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

/**
 * @codeCoverageIgnore
 */
class Tree extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    const ID = 'id';
    const PARENT_ID = 'parent_id';
    const NAME = 'name';
    const ACTIVE = 'active';
    const POSITION = 'position';
    const LEVEL = 'level';
    const CHILDREN = 'children';
    const PRODUCT_COUNT = 'product_count';

    /**
     * Get category ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get parent category ID
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->_get(self::PARENT_ID);
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Check whether category is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->_get(self::ACTIVE);
    }

    /**
     * Get category position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->_get(self::POSITION);
    }

    /**
     * Get category level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->_get(self::LEVEL);
    }

    /**
     * Get product count
     *
     * @return int
     */
    public function getProductCount()
    {
        return $this->_get(self::PRODUCT_COUNT);
    }

    /**
     * Get category level
     *
     * @return \Magento\Catalog\Service\V1\Data\Eav\Category\Tree[]
     */
    public function getChildren()
    {
        return $this->_get(self::CHILDREN);
    }
}
