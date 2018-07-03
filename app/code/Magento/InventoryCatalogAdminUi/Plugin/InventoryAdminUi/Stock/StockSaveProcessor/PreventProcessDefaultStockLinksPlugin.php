<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Plugin\InventoryAdminUi\Stock\StockSaveProcessor;

use Magento\InventoryAdminUi\Model\Stock\StockSourceLinkProcessor;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

/**
 * Prevent process of source links related to default stock.
 */
class PreventProcessDefaultStockLinksPlugin
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(DefaultStockProviderInterface $defaultStockProvider)
    {
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Prevent default stock links process.
     *
     * @param StockSourceLinkProcessor $subject
     * @param \Closure $proceed
     * @param int $stockId
     * @param array $linksData
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcess(StockSourceLinkProcessor $subject, \Closure $proceed, int $stockId, array $linksData)
    {
        if ($stockId !== $this->defaultStockProvider->getId()) {
            $proceed($stockId, $linksData);
        }
    }
}
