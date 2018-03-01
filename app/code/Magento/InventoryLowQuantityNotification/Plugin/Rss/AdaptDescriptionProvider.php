<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin\Rss;

use Magento\Catalog\Block\Adminhtml\Rss\NotifyStock\DescriptionProvider;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide description for rss item with source information.
 */
class AdaptDescriptionProvider
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver
    ) {
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
    }

    /**
     * @param DescriptionProvider $subject
     * @param callable $proceed
     * @param $item
     *
     * @return Phrase
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        DescriptionProvider $subject,
        callable $proceed,
        AbstractModel $item
    ): Phrase {
        $qty = 1 * $item->getData('qty');

        $description = __(
            '%1 has reached a quantity of %2 in source %3(Source Code: %4).',
            $item->getData('name'),
            $qty,
            $item->getData('source_name'),
            $item->getData(SourceItemInterface::SOURCE_CODE)
        );

        return $description;
    }
}
