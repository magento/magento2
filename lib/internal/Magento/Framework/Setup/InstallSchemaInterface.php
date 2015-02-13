<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Interface for DB schema installs of a module
 */
interface InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaResourceInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaResourceInterface $setup, ModuleContextInterface $context);
}
