<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration\Command;

use Magento\Framework\Data\ValueSourceInterface;
use Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration\ConfigValueProvider;

/**
 * Get config value by field name from Source Item Configuration.
 */
class GetConfigValue implements ValueSourceInterface
{
    /**
     * @var ConfigValueProvider
     */
    private $configValueProvider;

    /**
     * @param ConfigValueProvider $configValueProvider
     */
    public function __construct(
        ConfigValueProvider $configValueProvider
    ) {
        $this->configValueProvider = $configValueProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($name)
    {
        $value = (float)$this->configValueProvider->execute($name);

        return $value;
    }
}
