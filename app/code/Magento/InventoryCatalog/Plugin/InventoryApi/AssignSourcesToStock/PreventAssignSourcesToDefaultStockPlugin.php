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
     * @param array $sourceCodes
     * @param int $stockId
     * @return array
     * @throws InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(AssignSourcesToStockInterface $subject, array $sourceCodes, int $stockId)
    {
        if ($this->defaultStockProvider->getId() === $stockId) {
            if ((1 !== count($sourceCodes) || $this->defaultSourceProvider->getCode() !== $sourceCodes[0])) {
                throw new InputException(__('You can only assign Default Source to Default Stock'));
            }
        }
        return [$sourceCodes, $stockId];
    }
}
