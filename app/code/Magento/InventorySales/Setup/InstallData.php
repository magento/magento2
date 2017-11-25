<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\InventorySales\Setup\Operation\AssignWebsiteToDefaultStock;

/**
 * Assigns Main website to the Default stock
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var AssignWebsiteToDefaultStock
     */
    private $assignWebsiteToDefaultStock;

    /**
     * @param AssignWebsiteToDefaultStock $assignWebsiteToDefaultStock
     */
    public function __construct(
        AssignWebsiteToDefaultStock $assignWebsiteToDefaultStock
    ) {
        $this->assignWebsiteToDefaultStock = $assignWebsiteToDefaultStock;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->assignWebsiteToDefaultStock->execute();
    }
}
