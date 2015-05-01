<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

interface TaxRateTitleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     *
     * Tax rate field key.
     */
    const KEY_STORE_ID = 'store_id';

    const KEY_VALUE_ID = 'value';
    /**#@-*/

    /**
     * Get store id
     *
     * @api
     * @return string
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @api
     * @param string $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get title value
     *
     * @api
     * @return string
     */
    public function getValue();

    /**
     * Set title value
     *
     * @api
     * @param string $value
     * @return string
     */
    public function setValue($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRateTitleExtensionInterface $extensionAttributes);
}
