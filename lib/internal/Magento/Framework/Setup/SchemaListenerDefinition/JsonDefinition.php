<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
