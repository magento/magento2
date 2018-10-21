<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink as SourceCarrierLinkResourceModel;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class SourceCarrierLink extends AbstractExtensibleModel implements SourceCarrierLinkInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SourceCarrierLinkResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getCarrierCode(): ?string
    {
        return $this->getData(self::CARRIER_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setCarrierCode(?string $carrierCode): void
    {
        $this->setData(self::CARRIER_CODE, $carrierCode);
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): ?int
    {
        return $this->getData(self::POSITION) === null ?
            null:
            (int)$this->getData(self::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setPosition(?int $position): void
    {
        $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?SourceCarrierLinkExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceCarrierLinkInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceCarrierLinkExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
