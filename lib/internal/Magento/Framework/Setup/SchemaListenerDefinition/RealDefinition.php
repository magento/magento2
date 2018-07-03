<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\SchemaListenerDefinition;

/**
 * Convert definition for all real types: decimal, float, double.
 */
class RealDefinition implements DefinitionConverterInterface
{
    /**
     * Decimal, double and float have different default values.
     *
     * @var array
     */
    private static $shapeByType = [
        'float' => [
            'precision' => '0',
            'scale' => '0'
        ],
        'decimal' => [
            'precision' => '0',
            'scale' => '10'
        ],
        'double' => [
            'precision' => '0',
            'scale' => '0'
        ]
    ];

    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        if (isset($definition['length'])) {
            list($definition['precision'], $definition['scale']) = explode(",", $definition['length']);
        }
        return [
            'xsi:type' => $definition['type'],
            'name' => $definition['name'],
            //In previous adapter this 2 fields were switched, so we need to switch again
            'scale' => $definition['scale'] ?? self::$shapeByType[$definition['type']]['scale'],
            'precision' => $definition['precision'] ?? self::$shapeByType[$definition['type']]['precision'],
            'unsigned' => $definition['unsigned'] ?? false,
            'nullable' => $definition['nullable'] ?? true,
            'default' => isset($definition['default']) && $definition['default'] !== false ?
                (int) $definition['default'] : null,
            'primary' => $definition['primary'] ?? false
        ];
    }
}
