<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api\Data;

/**
 * Interface OptionTypeInterface
 * @api
 * @since 2.0.0
 */
interface OptionTypeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get type label
     *
     * @return string
     * @since 2.0.0
     */
    public function getLabel();

    /**
     * Set type label
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label);

    /**
     * Get type code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set type code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Bundle\Api\Data\OptionTypeExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Bundle\Api\Data\OptionTypeExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Bundle\Api\Data\OptionTypeExtensionInterface $extensionAttributes);
}
