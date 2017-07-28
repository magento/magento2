<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Interface for handling data removal during module uninstall
 *
 * @api
 * @since 2.0.0
 */
interface UninstallInterface
{
    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @since 2.0.0
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context);
}
