<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventorySales\Model\ResourceModel\SalesChannel as SalesChannelResourceModel;
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
    protected function _construct()
    {
        $this->_init(SalesChannelResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getSalesChannellId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setSalesChannelId(int $salesChannelId)
    {
        $this->setData(self::ID, $salesChannelId);
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
    public function setType(string $type)
    {
        $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritdoc
     */
    public function setCode(string $code)
    {
        $this->setData(self::CODE, $code);
    }

}
