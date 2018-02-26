<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Provide config value by name for Source Item Configuration.
 */
class ConfigValueProvider
{
    /**
     * Default Source Item Configuration path.
     */
    const XML_PATH_SOURCE_ITEM_CONFIGURATION = 'inventory/source_item_configuration/';

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
     * @param string $name
     *
     * @return mixed
     */
    public function execute(string $name)
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_SOURCE_ITEM_CONFIGURATION . $name);

        return $value;
    }
}
