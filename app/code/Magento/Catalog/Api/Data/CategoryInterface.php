<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Category data interface.
 *
 * @api
 * @since 100.0.2
 */
interface CategoryInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const KEY_PARENT_ID = 'parent_id';
    const KEY_NAME = 'name';
    const KEY_IS_ACTIVE = 'is_active';
    const KEY_POSITION = 'position';
    const KEY_LEVEL = 'level';
    const KEY_UPDATED_AT = 'updated_at';
    const KEY_CREATED_AT = 'created_at';
    const KEY_PATH = 'path';
    const KEY_AVAILABLE_SORT_BY = 'available_sort_by';
    const KEY_INCLUDE_IN_MENU = 'include_in_menu';
    const KEY_PRODUCT_COUNT = 'product_count';
    const KEY_CHILDREN_DATA = 'children_data';

    const ATTRIBUTES = [
        'id',
        self::KEY_PARENT_ID,
        self::KEY_NAME,
        self::KEY_IS_ACTIVE,
        self::KEY_POSITION,
        self::KEY_LEVEL,
        self::KEY_UPDATED_AT,
        self::KEY_CREATED_AT,
        self::KEY_AVAILABLE_SORT_BY,
        self::KEY_INCLUDE_IN_MENU,
        self::KEY_CHILDREN_DATA,
    ];
    /**#@-*/

    /**
     * Retrieve category id.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set category id.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get parent category ID
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Set parent category ID
     *
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * Get category name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Check whether category is active
     *
     * @return bool|null
     */
    public function getIsActive();

    /**
     * Set whether category is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Get category position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set category position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get category level
     *
     * @return int|null
     */
    public function getLevel();

    /**
     * Set category level
     *
     * @param int $level
     * @return $this
     */
    public function setLevel($level);

    /**
     * Retrieve children ids comma separated.
     *
     * @return string|null
     */
    public function getChildren();

    /**
     * Retrieve category creation date and time.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set category creation date and time.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Retrieve category last update date and time.
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set category last update date and time.
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Retrieve category full path.
     *
     * @return string|null
     */
    public function getPath();

    /**
     * Set category full path.
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path);

    /**
     * Retrieve available sort by for category.
     *
     * @return string[]|null
     */
    public function getAvailableSortBy();

    /**
     * Set available sort by for category.
     *
     * @param string[]|string $availableSortBy
     * @return $this
     */
    public function setAvailableSortBy($availableSortBy);

    /**
     * Get category is included in menu.
     *
     * @return bool|null
     */
    public function getIncludeInMenu();

    /**
     * Set category is included in menu.
     *
     * @param bool $includeInMenu
     * @return $this
     */
    public function setIncludeInMenu($includeInMenu);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\CategoryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes);
}
