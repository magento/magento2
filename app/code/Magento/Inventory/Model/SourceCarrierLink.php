<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;

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
        return $this;
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
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
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
