<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\SchemaListenerDefinition;

use Magento\Framework\DB\Ddl\Table;

/**
 * Convert definition for all text types: timestamp, datetime.
 */
class TimestampDefinition implements DefinitionConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        $cDefault = $definition['default'] ?? null;
        $cNullable = $definition['nullable'] ?? true;
        $onUpdate = false;
        if ($cDefault === null && !$cNullable) {
            $cDefault = 'NULL';
        } elseif ($cDefault == Table::TIMESTAMP_INIT) {
            $cDefault = 'CURRENT_TIMESTAMP';
        } elseif ($cDefault == Table::TIMESTAMP_UPDATE) {
            $cDefault = '0';
            $onUpdate = true;
        } elseif ($cDefault == Table::TIMESTAMP_INIT_UPDATE) {
            $cDefault = 'CURRENT_TIMESTAMP';
            $onUpdate = true;
        } elseif (!$cNullable && !$cDefault && $definition['type'] === 'timestamp') {
            $cDefault = 'CURRENT_TIMESTAMP';
            $onUpdate = true;
            $cNullable = true;
        }

        return [
            'xsi:type' => $definition['type'],
            'name' => $definition['name'],
            'on_update' => $onUpdate,
            'nullable' => $cNullable,
            'default' => $cDefault
        ];
    }
}
