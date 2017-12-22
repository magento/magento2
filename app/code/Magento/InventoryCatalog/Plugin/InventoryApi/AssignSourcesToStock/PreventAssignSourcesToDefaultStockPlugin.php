<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi\AssignSourcesToStock;

use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\AssignSourcesToStockInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

class PreventAssignSourcesToDefaultStockPlugin
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * @param AssignSourcesToStockInterface $subject
     * @param array $sourceIds
     * @param int $stockId
     * @return array
     * @throws InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(AssignSourcesToStockInterface $subject, array $sourceIds, int $stockId)
    {
        if ($this->defaultStockProvider->getId() !== $stockId
            || (1 == count($sourceIds) && $this->defaultSourceProvider->getId() == $sourceIds[0])) {
            return [$sourceIds, $stockId];
        }

        throw new InputException(__('You can only assign Default Source to Default Stock'));
    }
}
