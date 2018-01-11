<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\SchemaListenerDefinition;

/**
 * Convert definition for all decimal types: decimal, float, double
 */
class DecimalDefinition implements DefinitionConverterInterface
{
    /**
     * Decimal and float has different default values
     *
     * @var array
     */
    private static $shapeByType = [
        'float' => [
            'precision' => '10',
            'scale' => '0'
        ],
        'decimal' => [
            'precision' => '12',
            'scale' => '4'
        ]
    ];

    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        return [
            'xsi:type' => $definition['type'],
            'name' => $definition['name'],
            //In previos adapter this 2 fields were switched, so we need to switch again
            'scale' => $definition['precision'] ?? self::$shapeByType[$definition['type']]['precision'],
            'precission' => $definition['scale'] ?? self::$shapeByType[$definition['type']]['scale'],
            'unsigned' => $definition['unsigned'] ?? false,
            'nullable' => $definition['nullable'] ?? true,
            'default' => isset($definition['default']) && $definition['default'] !== false ? (int) $definition['default'] : null,
            'primary' => $definition['primary'] ?? false
        ];
    }
}
