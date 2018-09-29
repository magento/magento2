<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventorySalesApi\Api\Data\SalesChannelExtensionInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class SalesChannel extends AbstractExtensibleModel implements SalesChannelInterface
{
    /**
     * @inheritdoc
     */
    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): void
    {
        $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getCode(): ?string
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritdoc
     */
    public function setCode(string $code): void
    {
        $this->setData(self::CODE, $code);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?SalesChannelExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SalesChannelInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SalesChannelExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
