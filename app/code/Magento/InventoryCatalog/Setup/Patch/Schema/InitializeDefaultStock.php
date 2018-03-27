<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\InventoryCatalog\Setup\Operation\AssignDefaultSourceToDefaultStock;
use Magento\InventoryCatalog\Setup\Operation\CreateDefaultSource;
use Magento\InventoryCatalog\Setup\Operation\CreateDefaultStock;

/**
 * Patch schema with information about default stock
 */
class InitializeDefaultStock implements SchemaPatchInterface
{
    /**
     * @var CreateDefaultSource
     */
    private $createDefaultSource;

    /**
     * @var CreateDefaultStock
     */
    private $createDefaultStock;

    /**
     * @var AssignDefaultSourceToDefaultStock
     */
    private $assignDefaultSourceToDefaultStock;

    /**
     * @param CreateDefaultSource $createDefaultSource
     * @param CreateDefaultStock $createDefaultStock
     * @param AssignDefaultSourceToDefaultStock $assignDefaultSourceToDefaultStock
     */
    public function __construct(
        CreateDefaultSource $createDefaultSource,
        CreateDefaultStock $createDefaultStock,
        AssignDefaultSourceToDefaultStock $assignDefaultSourceToDefaultStock
    ) {
        $this->createDefaultSource = $createDefaultSource;
        $this->createDefaultStock = $createDefaultStock;
        $this->assignDefaultSourceToDefaultStock = $assignDefaultSourceToDefaultStock;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->createDefaultSource->execute();
        $this->createDefaultStock->execute();
        $this->assignDefaultSourceToDefaultStock->execute();

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
