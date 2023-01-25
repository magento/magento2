<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\SchemaListenerDefinition;

/**
 * Char type definition.
 */
class CharDefinition implements DefinitionConverterInterface
{
    private const DEFAULT_TEXT_LENGTH = 255;

    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        return [
            'xsi:type' => $definition['type'],
            'name' => $definition['name'],
            'length' => $definition['length'] ?? self::DEFAULT_TEXT_LENGTH,
            'default' => isset($definition['default']) ? (bool) $definition['default'] : null,
            'nullable' => $definition['nullable'] ?? true,
        ];
    }
}
