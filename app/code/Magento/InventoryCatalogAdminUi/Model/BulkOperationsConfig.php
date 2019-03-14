<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class BulkOperationsConfig
{
    const XML_PATH_ASYNC_ENABLED = 'cataloginventory/bulk_operations/async';
    const XML_PATH_BATCH_SIZE = 'cataloginventory/bulk_operations/batch_size';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isAsyncEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_ASYNC_ENABLED);
    }

    /**
     * @return int
     */
    public function getBatchSize(): int
    {
        return (int) max(1, (int) $this->scopeConfig->getValue(self::XML_PATH_BATCH_SIZE));
    }
}
