<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySales\Model\ResourceModel\ReplaceSalesChannelsOnStock as ReplaceResourceModel;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class ReplaceSalesChannelsOnStock implements ReplaceSalesChannelsOnStockInterface
{
    /**
     * @var ReplaceResourceModel
     */
    private $replace;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ReplaceResourceModel $replace
     * @param LoggerInterface $logger
     */
    public function __construct(
        ReplaceResourceModel $replace,
        LoggerInterface $logger
    ) {
        $this->replace = $replace;
        $this->logger = $logger;
    }

    /**
     * Replace existing or non existing Sales Channels for Stock
     *
     * @param array $salesChannels
     * @param int $stockId
     * @return void
     */
    public function execute(array $salesChannels, int $stockId)
    {
    }
}
