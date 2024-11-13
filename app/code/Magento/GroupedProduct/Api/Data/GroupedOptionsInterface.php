<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents `product item id with qty` of a grouped product.
 */
interface GroupedOptionsInterface extends ExtensibleDataInterface
{
    /**
     * Get associated product id
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Get associated product qty
     *
     * @return int|null
     */
    public function getQty(): ?int;

    /**
     * Set extension attributes
     *
     * @param \Magento\GroupedProduct\Api\Data\GroupedOptionsExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(GroupedOptionsExtensionInterface $extensionAttributes): void;

    /**
     * Get extension attributes
     *
     * @return \Magento\GroupedProduct\Api\Data\GroupedOptionsExtensionInterface|null
     */
    public function getExtensionAttributes(): ?GroupedOptionsExtensionInterface;
}
