<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Plugin\InventoryApi\StockSourceLinksDelete\PreventDelete;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\StockSourceLinksDeleteInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Prevent deleting links related to default stock.
 */
class DefaultStockPlugin
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * DefaultStockPlugin constructor.
     *
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Prevent deleting links related to default stock.
     *
     * @param StockSourceLinksDeleteInterface $subject
     * @param \Magento\InventoryApi\Api\Data\StockSourceLinkInterface[] $links
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(StockSourceLinksDeleteInterface $subject, array $links)
    {
        foreach ($links as $link) {
            if ($link->getStockId() === $this->defaultStockProvider->getId()) {
                throw new LocalizedException(
                    __(
                        'Can not delete link for %1 source, as it is related to default stock',
                        $link->getSourceCode()
                    )
                );
            }
        }
    }
}
