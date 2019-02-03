<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Api\Data;

use Magento\Cms\Api\Data\PageExtensibleExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface PageExtensibleInterface
 * @package Magento\Cms\Api\Data
 */
interface PageExtensibleInterface extends PageInterface, ExtensibleDataInterface
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
     * @return \Magento\Cms\Api\Data\PageExtensibleExtensionInterface|null
     */
    public function getExtensionAttributes(): ?PageExtensibleExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\Cms\Api\Data\PageExtensibleExtensionInterface $extensionAttributes
     *
     * @return void
     */
    public function setExtensionAttributes(PageExtensibleExtensionInterface $extensionAttributes): void;
}
