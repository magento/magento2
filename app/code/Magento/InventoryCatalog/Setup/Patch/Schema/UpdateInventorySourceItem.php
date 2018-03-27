<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\InventoryCatalog\Setup\Operation\UpdateInventorySourceItem as Operation;

/**
 * Patch Inventory Source Items with Inventory Stock Item Data
 */
class UpdateInventorySourceItem implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Operation
     */
    private $operation;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Operation $operation
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Operation $operation
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->operation = $operation;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->operation->execute($this->moduleDataSetup);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [
            InitializeDefaultStock::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
