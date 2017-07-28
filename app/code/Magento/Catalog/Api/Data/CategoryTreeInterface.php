<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 2.0.0
 */
interface CategoryTreeInterface
{
    /**
     * @return int|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get parent category ID
     *
     * @return int
     * @since 2.0.0
     */
    public function getParentId();

    /**
     * Set parent category ID
     *
     * @param int $parentId
     * @return $this
     * @since 2.0.0
     */
    public function setParentId($parentId);

    /**
     * Get category name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Check whether category is active
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsActive();

    /**
     * Set whether category is active
     *
     * @param bool $isActive
     * @return $this
     * @since 2.0.0
     */
    public function setIsActive($isActive);

    /**
     * Get category position
     *
     * @return int
     * @since 2.0.0
     */
    public function getPosition();

    /**
     * Set category position
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position);

    /**
     * Get category level
     *
     * @return int
     * @since 2.0.0
     */
    public function getLevel();

    /**
     * Set category level
     *
     * @param int $level
     * @return $this
     * @since 2.0.0
     */
    public function setLevel($level);

    /**
     * Get product count
     *
     * @return int
     * @since 2.0.0
     */
    public function getProductCount();

    /**
     * Set product count
     *
     * @param int $productCount
     * @return $this
     * @since 2.0.0
     */
    public function setProductCount($productCount);

    /**
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface[]
     * @since 2.0.0
     */
    public function getChildrenData();

    /**
     * @param \Magento\Catalog\Api\Data\CategoryTreeInterface[] $childrenData
     * @return $this
     * @since 2.0.0
     */
    public function setChildrenData(array $childrenData = null);
}
