<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\ValueSourceInterface;

/**
 * Get config value by field name from Source Item Configuration.
 */
class GetConfigValue implements ValueSourceInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($name)
    {
        $value = (float)$this->scopeConfig->getValue('inventory/source_item_configuration/' . $name);

        return $value;
    }
}
