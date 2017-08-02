<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api\Data;

/**
 * Interface OptionValueInterface
 * @api
 * @since 2.0.0
 */
interface OptionValueInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return int
     * @since 2.0.0
     */
    public function getValueIndex();

    /**
     * @param int $valueIndex
     * @return $this
     * @since 2.0.0
     */
    public function setValueIndex($valueIndex);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\ConfigurableProduct\Api\Data\OptionValueExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\OptionValueExtensionInterface $extensionAttributes
    );
}
