<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\SchemaListenerDefinition;

/**
 * Convert definition for all integer types: int, smallint, bigint, tinyint, mediumint.
 */
class IntegerDefinition implements DefinitionConverterInterface
{
    /**
     * If length is not specified we will use next numbers.
     *
     * @var array
     */
    private static $lengthDefaults = [
        'tinyint' => 3,
        'smallint' => 6,
        'mediumint' => 8,
        'int' => 11,
        'bigint' => 20
    ];

    /**
     * @var BooleanDefinition
     */
    private $booleanDefinition;

    /**
     * IntegerDefinition constructor.
     *
     * @param BooleanDefinition $booleanDefinition
     */
    public function __construct(BooleanDefinition $booleanDefinition)
    {
        $this->booleanDefinition = $booleanDefinition;
    }

    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        if ($definition['type'] === 'integer') {
            $definition['type'] = 'int';
        }

        if (isset($definition['length']) && (int) $definition['length'] === 1) {
            $definition['type'] = 'boolean';
            return $this->booleanDefinition->convertToDefinition($definition);
        }
        $length = $definition['length'] ?? self::$lengthDefaults[$definition['type']];
        $unsigned = $definition['unsigned'] ?? false;

        if ((bool) $unsigned && in_array($definition['type'], ['int', 'smallint'])) {
            $length--;
        }

        return [
            'xsi:type' => $definition['type'],
            'name' => $definition['name'],
            'padding' => $length,
            'unsigned' => $unsigned,
            'nullable' => $definition['nullable'] ?? true,
            'identity' => $definition['identity'] ?? false,
            'default' => isset($definition['default']) && $definition['default'] !== false ?
                (int) $definition['default'] : null,
            'primary' => $definition['primary'] ?? false
        ];
    }
}
