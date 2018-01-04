<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\SchemaListenerHandlers;

/**
 * Here we will put schema listener handlers
 */
interface SchemaListenerHandlerInterface
{
    /**
     * @param string $moduleName
     * @param array $tables
     * @param array $params Params consist data as old_column_name, new_column_name, table_name, etc
     * @param array $definition
     * @return mixed
     */
    public function handle($moduleName, $tables, array $params, $definition);
}
