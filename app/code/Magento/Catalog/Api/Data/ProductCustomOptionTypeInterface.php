<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 * @since 100.0.2
 */
interface ProductCustomOptionTypeInterface extends ExtensibleDataInterface
{
    /**
     * Get option type label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set option type label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get option type code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set option type code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get option type group
     *
     * @return string
     */
    public function getGroup();

    /**
     * Set option type group
     *
     * @param string $group
     * @return $this
     */
    public function setGroup($group);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductCustomOptionTypeExtensionInterface $extensionAttributes
    );
}
