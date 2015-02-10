<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\Module\DataSetup;

/**
 * Interface for data installs of a module
 */
interface InstallDataInterface
{
    /**
     * Installs data for a module
     *
     * @param ModuleDataResourceInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataResourceInterface $setup, ModuleContextInterface $context);
}
