<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\SchemaListenerDefinition;

/**
 * Convert definition for all integer types: int, smallint, bigint, tinyint
 */
class IntegerDefinition implements DefinitionConverterInterface
{
    /**
     * If length is not specified we will use next numbers
     *
     * @var array
     */
    private static $lengthDefaults = [
        'tinyint' => 3,
        'smallint' => 6,
        'integer' => 11,
        'bigint' => 20
    ];

    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        return [
            'xsi:type' => $definition['type'],
            'name' => $definition['name'],
            'padding' => $definition['length'] ?? self::$lengthDefaults[$definition['type']],
            'unsigned' => $definition['unsigned'] ?? false,
            'nullable' => $definition['nullable'] ?? true,
            'identity' => $definition['identity'] ?? false,
            'default' => $definition['default'] ?? null,
            'primary' => $definition['primary'] ?? false
        ];
    }
}
