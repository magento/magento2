<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Tax class key interface.
 * @api
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
     */
    public function getType();

    /**
     * Set type of tax class key
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get value of tax class key
     *
     * @return string
     */
    public function getValue();

    /**
     * Set value of tax class key
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\TaxClassKeyExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxClassKeyExtensionInterface $extensionAttributes);
}
