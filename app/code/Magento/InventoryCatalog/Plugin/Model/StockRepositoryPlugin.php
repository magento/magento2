<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Plugin\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Plugin Stock Repository
 */
class StockRepositoryPlugin
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
     * Prevent default source to be deleted
     *
     * @param StockRepositoryInterface $subject
     * @param int $stockId
     *
     * @return null
     * @throws CouldNotDeleteException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDeleteById(StockRepositoryInterface $subject, int $stockId)
    {
        if ($stockId === $this->defaultStockProvider->getId()) {
            throw new CouldNotDeleteException(__('Default Stock could not be deleted.'));
        }

        return null;
    }
}
