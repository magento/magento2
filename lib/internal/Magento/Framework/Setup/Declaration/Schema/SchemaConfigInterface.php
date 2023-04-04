<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;

/**
 * Schema configuration interface.
 *
 * Used to fetch schema object with data from either db or XML file.
 *
 * Declaration has 2 schema builders, that build schema from db and from XML.
 *
 * @api
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
