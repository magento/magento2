<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\SchemaListenerDefinition;

/**
 * Definition formatting interface.
 *
 * @api
 */
interface DefinitionConverterInterface
{
    /**
     * Takes definition and convert to new format.
     *
     * @param array $definition
     * @return array
     */
    public function convertToDefinition(array $definition);
}
