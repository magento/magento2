<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Api\Data;

use Magento\Cms\Api\Data\BlockExtensibleExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface BlockExtensibleInterface
 * @package Magento\Cms\Api\Data
 */
interface BlockExtensibleInterface extends BlockInterface, ExtensibleDataInterface
{
    /**
     * Get store ids
     *
     * @return int[]
     */
    public function getStores(): array;

    /**
     * Get extension attributes
     *
     * @return \Magento\Cms\Api\Data\BlockExtensibleExtensionInterface|null
     */
    public function getExtensionAttributes(): ?BlockExtensibleExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\Cms\Api\Data\BlockExtensibleExtensionInterface $extensionAttributes
     *
     * @return void
     */
    public function setExtensionAttributes(BlockExtensibleExtensionInterface $extensionAttributes): void;
}
