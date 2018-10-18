<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api\Data;

use Magento\Framework\Api\AbstractSimpleObject;

/**
 * TODO: temporal fix of extension classes generation during installation
 * Extension class for @see \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
 */
class StockItemConfigurationExtension extends AbstractSimpleObject implements StockItemConfigurationExtensionInterface
{
    /**
     * @inheritdoc
     */
    public function getIsInStock(): ?bool
    {
        return $this->_get('is_in_stock');
    }

    /**
     * @inheritdoc
     */
    public function setIsInStock(?bool $isInStock): void
    {
        $this->setData('is_in_stock', $isInStock);
    }
}
