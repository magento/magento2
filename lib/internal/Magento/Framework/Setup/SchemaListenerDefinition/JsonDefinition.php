<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Setup\SchemaListenerDefinition;

/**
 * Json type definition.
 */
class JsonDefinition implements DefinitionConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        return [
            'xsi:type' => $definition['type'],
            'name' => $definition['name'],
            'nullable' => $definition['nullable'] ?? true
        ];
    }
}
