<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Setup\SchemaListenerDefinition;

/**
 * Boolean type definition.
 */
class BooleanDefinition implements DefinitionConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        return [
            'xsi:type' => $definition['type'],
            'name' => $definition['name'],
            'nullable' => $definition['nullable'] ?? true,
            'default' => isset($definition['default']) ? (bool) $definition['default'] : null
        ];
    }
}
