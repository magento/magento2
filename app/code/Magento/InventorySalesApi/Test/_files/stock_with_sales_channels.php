<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var StockInterfaceFactory $stockFactory */
$stockFactory = Bootstrap::getObjectManager()->get(StockInterfaceFactory::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);

/** @var StockInterface $stock */
$stock = $stockFactory->create();
$dataObjectHelper->populateWithArray(
    $stock,
    [
        StockInterface::STOCK_ID => 10,
        StockInterface::NAME => 'stock_with_channels_name',
        ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => [
            'sales_channels' => [
                [
                    SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                    SalesChannelInterface::CODE => 'eu_website',
                ],
                [
                    SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                    SalesChannelInterface::CODE => 'us_website',
                ],
            ],
        ],
    ],
    StockInterface::class
);
$stockRepository->save($stock);
