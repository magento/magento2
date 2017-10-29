<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Implementation of basic operations for Source Item Notification entity for specific db layer
 */
class SourceItemConfiguration extends AbstractExtensibleModel implements SourceItemConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getSourceItemId()
    {
        return $this->getData(self::SOURCE_ITEM_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSourceItemId(string $sourceItemId)
    {
        if (!$this->getSourceItemId()) {
            $this->setData(self::SOURCE_ITEM_ID, $sourceItemId);
        }
    }

    /**
     * @inheritdoc
     */
    public function getNotifyQuantity()
    {
        return $this->getData(self::INVENTORY_NOTIFY_QTY);
    }

    /**
     * @inheritdoc
     */
    public function setNotifyQuantity($quantity)
    {
        $this->setData(self::INVENTORY_NOTIFY_QTY, $quantity);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceItemConfigurationInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceItemExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}