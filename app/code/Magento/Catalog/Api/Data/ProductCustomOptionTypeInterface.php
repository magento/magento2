<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 * @since 2.0.0
 */
interface ProductCustomOptionTypeInterface extends ExtensibleDataInterface
{
    /**
     * Get option type label
     *
     * @return string
     * @since 2.0.0
     */
    public function getLabel();

    /**
     * Set option type label
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label);

    /**
     * Get option type code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set option type code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get option type group
     *
     * @return string
     * @since 2.0.0
     */
    public function getGroup();

    /**
     * Set option type group
     *
     * @param string $group
     * @return $this
     * @since 2.0.0
     */
    public function setGroup($group);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface $extensionAttributes
    );
}
