<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryCatalog\Api\DefaultStockRepositoryInterface;
use Magento\Inventory\Model\Stock\Command\GetInterface;

/**
 * Class DefaultStockRepository
 */
class DefaultStockRepository implements DefaultStockRepositoryInterface
{

    /**
     * @var GetInterface
     */
    private $commandGet;

    /**
     * @param GetInterface $commandGet
     */
    public function __construct(GetInterface $commandGet)
    {
        $this->commandGet = $commandGet;
    }

    /**
     * Get default stock
     *
     * @return StockInterface
     */
    public function get(): StockInterface
    {
        return $this->commandGet->execute(DefaultStockRepositoryInterface::DEFAULT_STOCK);
    }
}
