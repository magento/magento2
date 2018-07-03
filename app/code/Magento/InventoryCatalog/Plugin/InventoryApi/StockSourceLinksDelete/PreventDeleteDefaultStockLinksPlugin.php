<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi\StockSourceLinksDelete;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\StockSourceLinksDeleteInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

/**
 * Prevent deleting links related to default stock.
 */
class PreventDeleteDefaultStockLinksPlugin
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        DefaultStockProviderInterface $defaultStockProvider,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * Prevent deleting links related to default stock or default source.
     *
     * @param StockSourceLinksDeleteInterface $subject
     * @param \Magento\InventoryApi\Api\Data\StockSourceLinkInterface[] $links
     * @throws LocalizedException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(StockSourceLinksDeleteInterface $subject, array $links)
    {
        foreach ($links as $link) {
            if ($link->getStockId() === $this->defaultStockProvider->getId()
                || $link->getSourceCode() === $this->defaultSourceProvider->getCode()
            ) {
                throw new LocalizedException(__('Can not delete link related to Default Source or Default Stock'));
            }
        }
    }
}
