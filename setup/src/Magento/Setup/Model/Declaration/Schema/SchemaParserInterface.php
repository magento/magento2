<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Setup\Model\Declaration\Schema\Dto\Structure;

/**
 * Parser hydrate structure object with data from either db or XML file
 * Usually parser use StructureBuilders
 *
 * Declaration has 2 structure builders, that build schema from db and from XML
 */
interface SchemaParserInterface
{
    /**
     * Parse XML or DB changes into structure
     *
     * @param Structure $structure
     * @return mixed
     */
    public function parse(Structure $structure);
}
