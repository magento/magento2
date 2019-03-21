<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class IsAsyncLegacyAlignment
{
    /**
     * Configuration path for legacy stock asynchronous operation enabling status
     */
    private const XML_PATH = 'cataloginventory/legacy_stock/async';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * IsAsyncLegacyAlignment constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return true if legacy inventory is aligned asynchronously
     * @return bool
     */
    public function execute(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH);
    }
}
