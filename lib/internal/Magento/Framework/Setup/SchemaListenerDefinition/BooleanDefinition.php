<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
