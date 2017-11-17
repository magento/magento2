<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Class provide Before Plugin on StockRepositoryInterface::deleteByItem to prevent default stock could be deleted
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
     * @return void
     * @throws CouldNotDeleteException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDeleteById(StockRepositoryInterface $subject, int $stockId)
    {
        if ($stockId === $this->defaultStockProvider->getId()) {
            throw new CouldNotDeleteException(__('Default Stock could not be deleted.'));
        }
    }
}
