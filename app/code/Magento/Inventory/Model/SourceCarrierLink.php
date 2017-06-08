<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;

/**
 * @codeCoverageIgnore
 */
class SourceCarrierLink extends AbstractExtensibleModel implements SourceCarrierLinkInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Inventory\Model\ResourceModel\SourceCarrierLink::class);
    }

    /**
     * @inheritDoc
     */
    public function getCarrierCode()
    {
        return $this->getData(SourceCarrierLinkInterface::CARRIER_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCarrierCode($carrierCode)
    {
        $this->setData(SourceCarrierLinkInterface::CARRIER_CODE, $carrierCode);
    }

    /**
     * @inheritDoc
     */
    public function getPosition()
    {
        return $this->getData(SourceCarrierLinkInterface::POSITION);
    }

    /**
     * @inheritDoc
     */
    public function setPosition($position)
    {
        $this->setData(SourceCarrierLinkInterface::POSITION, $position);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceCarrierLinkInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
