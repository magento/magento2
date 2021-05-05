<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
