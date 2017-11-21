<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryApi\Api\AssignSourcesToStockInterface;

/**
 * Assign default source to stock processor
 */
class AssignSourceToStock
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
     * @var AssignSourcesToStockInterface
     */
    private $assignSourcesToStock;

    /**
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param AssignSourcesToStockInterface $assignSourcesToStock
     */
    public function __construct(
        DefaultStockProviderInterface $defaultStockProvider,
        DefaultSourceProviderInterface $defaultSourceProvider,
        AssignSourcesToStockInterface $assignSourcesToStock
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->assignSourcesToStock = $assignSourcesToStock;
    }

    /**
     * Assign default source to stock
     *
     * @return void
     */
    public function execute()
    {
        $this->assignSourcesToStock->execute(
            [$this->defaultSourceProvider->getId()],
            $this->defaultStockProvider->getId()
        );
    }
}
