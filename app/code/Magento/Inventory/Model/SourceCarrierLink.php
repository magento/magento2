<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
    public function getCarrierCode()
    {
        return $this->getData(SourceCarrierLinkInterface::CARRIER_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setCarrierCode($carrierCode)
    {
        $this->setData(SourceCarrierLinkInterface::CARRIER_CODE, $carrierCode);
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->getData(SourceCarrierLinkInterface::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->setData(SourceCarrierLinkInterface::POSITION, $position);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceCarrierLinkExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
