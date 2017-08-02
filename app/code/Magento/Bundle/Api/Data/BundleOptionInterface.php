<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Api\Data;

/**
 * Interface BundleOptionInterface
 * @api
 * @since 2.0.0
 */
interface BundleOptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get bundle option id.
     *
     * @return int
     * @since 2.0.0
     */
    public function getOptionId();

    /**
     * Set bundle option id.
     *
     * @param int $optionId
     * @return int
     * @since 2.0.0
     */
    public function setOptionId($optionId);

    /**
     * Get bundle option quantity.
     *
     * @return int
     * @since 2.0.0
     */
    public function getOptionQty();

    /**
     * Set bundle option quantity.
     *
     * @param int $optionQty
     * @return int
     * @since 2.0.0
     */
    public function setOptionQty($optionQty);

    /**
     * Get bundle option selection ids.
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getOptionSelections();

    /**
     * Set bundle option selection ids.
     *
     * @param int[] $optionSelections
     * @return int[]
     * @since 2.0.0
     */
    public function setOptionSelections(array $optionSelections);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Bundle\Api\Data\BundleOptionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Bundle\Api\Data\BundleOptionExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Bundle\Api\Data\BundleOptionExtensionInterface $extensionAttributes
    );
}
