<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Config for inventory check on quote items load
 * @package Magento\Quote\Model
 */
class Config
{
    const XML_PATH_INVENTORY_CHECK_ENABLED = 'cataloginventory/options/enable_inventory_check';

    /** @var ScopeConfigInterface */
    private $config;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Check if Inventory check is enabled
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->config->getValue(self::XML_PATH_INVENTORY_CHECK_ENABLED);
    }
}
