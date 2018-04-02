<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Plugin\InventoryApi\StockSourceLinksSave\PreventSave;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Prevent saving links related to default stock, except installation process.
 */
class DefaultStockPlugin
{
    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * DefaultStockPlugin constructor.
     *
     * @param MaintenanceMode $maintenanceMode
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        MaintenanceMode $maintenanceMode,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
        $this->maintenanceMode = $maintenanceMode;
    }

    /**
     * Prevent saving links related to default stock.
     *
     * @param StockSourceLinksSaveInterface $subject
     * @param \Magento\InventoryApi\Api\Data\StockSourceLinkInterface[] $links
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(StockSourceLinksSaveInterface $subject, array $links)
    {
        //Exclude installation.
        if ($this->maintenanceMode->isOn()) {
            return;
        }
        foreach ($links as $link) {
            if ($link->getStockId() === $this->defaultStockProvider->getId()) {
                throw new LocalizedException(
                    __(
                        'Can not save link for %1 source, as it is related to default stock',
                        $link->getSourceCode()
                    )
                );
            }
        }
    }
}
