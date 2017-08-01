<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Api\Data;

/**
 * Interface OptionInterface
 * @api
 * @since 2.0.0
 */
interface OptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get option id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     * @since 2.0.0
     */
    public function setOptionId($optionId);

    /**
     * Get option title
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title);

    /**
     * Get is required option
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getRequired();

    /**
     * Set whether option is required
     *
     * @param bool $required
     * @return $this
     * @since 2.0.0
     */
    public function setRequired($required);

    /**
     * Get input type
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getType();

    /**
     * Set input type
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type);

    /**
     * Get option position
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getPosition();

    /**
     * Set option position
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position);

    /**
     * Get product sku
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Get product links
     *
     * @return \Magento\Bundle\Api\Data\LinkInterface[]|null
     * @since 2.0.0
     */
    public function getProductLinks();

    /**
     * Set product links
     *
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $productLinks
     * @return $this
     * @since 2.0.0
     */
    public function setProductLinks(array $productLinks = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Bundle\Api\Data\OptionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Bundle\Api\Data\OptionExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Bundle\Api\Data\OptionExtensionInterface $extensionAttributes);
}
