<?php
/**
 * Category data interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 2.0.0
 */
interface CategoryInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
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
     * @return int|null
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
     * @return bool|null
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
     * @return int|null
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
     * @return int|null
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
     * @return string|null
     * @since 2.0.0
     */
    public function getChildren();

    /**
     * @return string|null
     * @since 2.0.0
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     * @since 2.0.0
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     * @since 2.0.0
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string|null
     * @since 2.0.0
     */
    public function getPath();

    /**
     * @param string $path
     * @return $this
     * @since 2.0.0
     */
    public function setPath($path);

    /**
     * @return string[]|null
     * @since 2.0.0
     */
    public function getAvailableSortBy();

    /**
     * @param string[]|string $availableSortBy
     * @return $this
     * @since 2.0.0
     */
    public function setAvailableSortBy($availableSortBy);

    /**
     * @return bool|null
     * @since 2.0.0
     */
    public function getIncludeInMenu();

    /**
     * @param bool $includeInMenu
     * @return $this
     * @since 2.0.0
     */
    public function setIncludeInMenu($includeInMenu);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\CategoryExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes);
}
