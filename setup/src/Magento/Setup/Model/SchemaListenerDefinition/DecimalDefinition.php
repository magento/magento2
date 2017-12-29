<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\SchemaListenerDefinition;

use Magento\Framework\DB\Ddl\Table;

/**
 * Convert definition for all decimal types: decimal, float, double
 */
class DecimalDefinition implements DefinitionConverterInterface
{
    /**
     * Default scale for all decimals
     */
    const DEFAULT_SCALE = '0';

    /**
     * Default precision for all decimals
     */
    const DEFAULT_PRECISION = '10';

    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        return [
            'xsi:type' => $definition['type'],
            'name' => $definition['name'],
            'scale' => $definition['scale'] ?? self::DEFAULT_SCALE,
            'precision' => $definition['precision'] ?? self::DEFAULT_PRECISION,
            'unsigned' => $definition['unsigned'] ?? false,
            'nullable' => $definition['nullable'] ?? true,
            'default' => $definition['default'] ?? null
        ];
    }
}
