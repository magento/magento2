<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api\Data;

use Magento\Framework\Api\ExtensionAttributesInterface;

/**
 * TODO: temporal fix of extension classes generation during installation
 * ExtensionInterface class for @see \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
 */
interface StockItemConfigurationExtensionInterface extends ExtensionAttributesInterface
{
    /**
     * @return bool|null
     */
    public function getIsInStock(): ?bool;

    /**
     * @param bool|null $isInStock
     * @return void
     */
    public function setIsInStock(?bool $isInStock): void;
}
