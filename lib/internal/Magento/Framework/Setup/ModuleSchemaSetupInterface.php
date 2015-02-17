<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * DB schema resource interface for a module
 */
interface ModuleSchemaSetupInterface extends SchemaSetupInterface
{
    /**
     * Applies module recurring post schema updates
     *
     * @return $this
     * @throws \Exception
     */
    public function applyRecurringUpdates();

    /**
     * Applies module resource install, upgrade and data scripts
     *
     * @return $this
     */
    public function applyUpdates();
}
