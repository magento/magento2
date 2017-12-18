<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Setup\Model\Declaration\Schema\Dto\Schema;
use Magento\Setup\Model\Declaration\Schema\SchemaParserInterface;

/**
 * Parser is responsible for builind schema.
 * @see Schema
 *
 * @inheritdoc
 */
class Parser implements SchemaParserInterface
{
    /**
     * @var SchemaBuilder
     */
    private $schemaBuilder;

    /**
     * @param SchemaBuilder $schemaBuilder
     */
    public function __construct(SchemaBuilder $schemaBuilder)
    {
        $this->schemaBuilder = $schemaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function parse(Schema $schema)
    {
        return $this->schemaBuilder->build($schema);
    }
}
