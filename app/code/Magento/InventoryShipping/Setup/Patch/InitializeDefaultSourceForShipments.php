<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Setup\Patch;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\InventoryShipping\Setup\Operation\AssignDefaultSourceToShipments;

/**
 * Initialize Default Source For Shipments
 */
class InitializeDefaultSourceForShipments implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var AssignDefaultSourceToShipments
     */
    private $assignDefaultSourceToShipments;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AssignDefaultSourceToShipments $assignDefaultSourceToShipments
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        AssignDefaultSourceToShipments $assignDefaultSourceToShipments
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->assignDefaultSourceToShipments = $assignDefaultSourceToShipments;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->assignDefaultSourceToShipments->execute($this->moduleDataSetup);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
