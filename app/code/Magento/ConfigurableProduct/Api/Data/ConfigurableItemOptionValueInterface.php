<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api\Data;

/**
 * Interface ConfigurableItemOptionValueInterface
 * @api
 * @since 2.0.0
 */
interface ConfigurableItemOptionValueInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const OPTION_ID = 'option_id';

    const OPTION_VALUE = 'option_value';

    /**#@-*/

    /**
     * Get option SKU
     *
     * @return string
     * @since 2.0.0
     */
    public function getOptionId();

    /**
     * Set option SKU
     *
     * @param string $value
     * @return void
     * @since 2.0.0
     */
    public function setOptionId($value);

    /**
     * Get item id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getOptionValue();

    /**
     * Set item id
     *
     * @param int|null $value
     * @return void
     * @since 2.0.0
     */
    public function setOptionValue($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface $extensionAttributes
    );
}
