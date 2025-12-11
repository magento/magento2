<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Setup\SchemaListenerDefinition;

/**
 * Date type definition.
 */
class DateDefinition implements DefinitionConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convertToDefinition(array $definition)
    {
        return [
            'xsi:type' => 'date',
            'name' => $definition['name'],
        ];
    }
}
