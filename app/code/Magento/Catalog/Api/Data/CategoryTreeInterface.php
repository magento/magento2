<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

interface CategoryTreeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * Get parent category ID
     *
     * @return int
     */
    public function getParentId();

    /**
     * Get category name
     *
     * @return string
     */
    public function getName();

    /**
     * Check whether category is active
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Get category position
     *
     * @return int
     */
    public function getPosition();

    /**
     * Get category level
     *
     * @return int
     */
    public function getLevel();
    /**
     * Get product count
     *
     * @return int
     */
    public function getProductCount();

    /**
     * Get category level
     *
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface[]
     */
    public function getChildrenData();
}
