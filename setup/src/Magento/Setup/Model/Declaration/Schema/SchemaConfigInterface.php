<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Setup\Model\Declaration\Schema\Dto\Schema;

/**
 * Parser hydrate schema object with data from either db or XML file
 * Usually parser use SchemaBuilders
 *
 * Declaration has 2 schema builders, that build schema from db and from XML
 */
interface SchemaConfigInterface
{
    /**
     * Parse DB schema
     *
     * @return Schema
     */
    public function getDbConfig();

    /**
     * Parse XML schema
     *
     * @return Schema
     */
    public function getDeclarationConfig();
}
