<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Interface CustomOptionInterface
 * @api
 * @since 2.0.0
 */
interface CustomOptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const OPTION_ID = 'option_id';
    const OPTION_VALUE = 'option_value';
    /**#@-*/

    /**
     * Get option id
     *
     * @return string
     * @since 2.0.0
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param string $value
     * @return bool
     * @since 2.0.0
     */
    public function setOptionId($value);

    /**
     * Get option value
     *
     * @return string
     * @since 2.0.0
     */
    public function getOptionValue();

    /**
     * Set option value
     *
     * @param string $value
     * @return bool
     * @since 2.0.0
     */
    public function setOptionValue($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\CustomOptionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CustomOptionExtensionInterface|null $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\CustomOptionExtensionInterface $extensionAttributes
    );
}
