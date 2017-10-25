<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySales\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventorySales\Model\ResourceModel\StockChannel as StockChannelResourceModel;
use Magento\InventoryApi\Api\Data\StockChannelInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class StockChannel extends AbstractExtensibleModel implements StockChannelInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(StockChannelResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getStockChannelId()
    {
        return $this->getData(self::STOCK_CHANNEL_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStockChannelId($stockChannelId)
    {
        $this->setData(self::STOCK_CHANNEL_ID, $stockChannelId);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setCode($code)
    {
        $this->setData(self::CODE, $code);
    }
}
