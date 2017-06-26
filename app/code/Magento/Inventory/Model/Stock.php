<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Class Source
 *
 * @codeCoverageIgnore
 */
class Stock extends AbstractExtensibleModel implements StockInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Inventory\Model\ResourceModel\Stock::class);
    }

    /**
     * @inheritdoc
     */
    public function getStockId()
    {
        return $this->getData(StockInterface::STOCK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStockId($stockId)
    {
        $this->setData(StockInterface::STOCK_ID, $stockId);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(StockInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(StockInterface::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(StockInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\StockExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
