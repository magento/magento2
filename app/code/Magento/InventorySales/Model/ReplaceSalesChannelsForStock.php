<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySales\Model\ResourceModel\ReplaceSalesChannelsDataForStock;

/**
 * @inheritdoc
 */
class ReplaceSalesChannelsForStock implements ReplaceSalesChannelsForStockInterface
{
    /**
     * @var ReplaceSalesChannelsDataForStock
     */
    private $replaceSalesChannelsDataForStock;

    /**
     * @param ReplaceSalesChannelsDataForStock $replace
     */
    public function __construct(
        ReplaceSalesChannelsDataForStock $replace
    ) {
        $this->replaceSalesChannelsDataForStock = $replace;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $salesChannels, int $stockId)
    {
        $this->replaceSalesChannelsDataForStock->execute($salesChannels, $stockId);
    }
}
