<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 * @since 101.1.0
 */
interface CategoryLinkInterface extends ExtensibleDataInterface
{
    /**
     * @return int|null
     * @since 101.1.0
     */
    public function getPosition();

    /**
     * @param int $position
     * @return $this
     * @since 101.1.0
     */
    public function setPosition($position);

    /**
     * Get category id
     *
     * @return string
     * @since 101.1.0
     */
    public function getCategoryId();

    /**
     * Set category id
     *
     * @param string $categoryId
     * @return $this
     * @since 101.1.0
     */
    public function setCategoryId($categoryId);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\CategoryLinkExtensionInterface|null
     * @since 101.1.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CategoryLinkExtensionInterface $extensionAttributes
     * @return $this
     * @since 101.1.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\CategoryLinkExtensionInterface $extensionAttributes
    );
}
