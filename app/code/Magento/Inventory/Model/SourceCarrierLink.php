<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractModel;
use \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;

class SourceCarrierLink extends AbstractModel implements SourceCarrierLinkInterface
{

    /**
     * Name of the resource collection model
     *
     * @codingStandardsIgnore
     * @var string
     */
    protected $_collectionName = 'Magento\Inventory\Model\Resource\SourceCarrierLink\Collection';

    /**
     * Initialize resource model
     *
     * @codingStandardsIgnore
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Inventory\Model\Resource\SourceCarrierLink');
    }
    

    /**
     * @inheritDoc
     */
    public function getCarrierCode()
    {
        $this->getData(SourceCarrierLinkInterface::CARRIER_CODE);
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
        $this->getData(SourceCarrierLinkInterface::POSITION);
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