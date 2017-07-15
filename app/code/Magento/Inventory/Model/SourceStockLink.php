<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventoryApi\Api\Data\SourceStockLinkInterface;

/**
 * @inheritdoc
 */
class SourceStockLink extends AbstractExtensibleModel implements SourceStockLinkInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Inventory\Model\ResourceModel\SourceStockLink::class);
    }

    /**
     * @inheritdoc
     */
    public function getLinkId()
    {
        return $this->getData(SourceStockLinkInterface::LINK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setLinkId($linkId)
    {
        $this->setData(SourceStockLinkInterface::LINK_ID, $linkId);
    }

    /**
     * @inheritdoc
     */
    public function getSourceId()
    {
        return $this->getData(SourceStockLinkInterface::SOURCE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSourceId($sourceId)
    {
        $this->setData(SourceStockLinkInterface::SOURCE_ID, $sourceId);
    }

    /**
     * @inheritdoc
     */
    public function getStockId()
    {
        return $this->getData(SourceStockLinkInterface::STOCK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStockId($stockId)
    {
        $this->setData(SourceStockLinkInterface::STOCK_ID, $stockId);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceStockLinkInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\SourceStockLinkExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
