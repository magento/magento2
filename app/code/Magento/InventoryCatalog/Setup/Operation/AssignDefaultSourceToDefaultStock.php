<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterfaceFactory;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

/**
 * Assign default source to default stock
 */
class AssignDefaultSourceToDefaultStock
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
     * @var StockSourceLinkInterfaceFactory
     */
    private $stockSourceLinkFactory;

    /**
     * @var StockSourceLinksSaveInterface
     */
    private $stockSourceLinksSave;

    /**
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param StockSourceLinkInterfaceFactory $stockSourceLinkFactory
     * @param StockSourceLinksSaveInterface $stockSourceLinksSave
     */
    public function __construct(
        DefaultStockProviderInterface $defaultStockProvider,
        DefaultSourceProviderInterface $defaultSourceProvider,
        StockSourceLinkInterfaceFactory $stockSourceLinkFactory,
        StockSourceLinksSaveInterface $stockSourceLinksSave
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->stockSourceLinkFactory = $stockSourceLinkFactory;
        $this->stockSourceLinksSave = $stockSourceLinksSave;
    }

    /**
     * Assign default source to stock
     *
     * @return void
     */
    public function execute()
    {
        /** @var StockSourceLinkInterface $link */
        $link = $this->stockSourceLinkFactory->create();

        $link->setStockId($this->defaultStockProvider->getId());
        $link->setSourceCode($this->defaultSourceProvider->getCode());
        $link->setPriority(1);

        $this->stockSourceLinksSave->execute([$link]);
    }
}
