<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\SchemaListenerDefinition;

/**
 * The main responsibility of this class is formatting definition
 */
interface DefinitionConverterInterface
{
    /**
     * Takes definition and convert to new format
     *
     * @param array $definition
     * @return array
     */
    public function convertToDefinition(array $definition);
}