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
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

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
     * @var StockSourceLinksSaveInterface
     */
    private $stockSourceLinksSave;

    /**
     * @var StockSourceLinkInterfaceFactory
     */
    private $stockSourceLinkFactory;

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
        $this->stockSourceLinksSave = $stockSourceLinksSave;
        $this->stockSourceLinkFactory = $stockSourceLinkFactory;
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
