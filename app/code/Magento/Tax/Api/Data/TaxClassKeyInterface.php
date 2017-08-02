<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Tax class key interface.
 * @api
 * @since 2.0.0
 */
interface TaxClassKeyInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for type of tax class key
     */
    const TYPE_ID   = 'id';
    const TYPE_NAME = 'name';
    /**#@-*/

    /**
     * Get type of tax class key
     *
     * @return string
     * @since 2.0.0
     */
    public function getType();

    /**
     * Set type of tax class key
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type);

    /**
     * Get value of tax class key
     *
     * @return string
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Set value of tax class key
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxClassKeyExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxClassKeyExtensionInterface $extensionAttributes);
}
