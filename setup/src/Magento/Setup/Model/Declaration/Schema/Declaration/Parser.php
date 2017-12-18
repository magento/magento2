<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Declaration;

use Magento\Setup\Model\Declaration\Schema\Dto\Schema;
use Magento\Setup\Model\Declaration\Schema\FileSystem\XmlReader;
use Magento\Setup\Model\Declaration\Schema\SchemaParserInterface;

/**
 * @TODO: add composite readers
 * Read schema data from XML and convert it to objects representation
 */
class Parser implements SchemaParserInterface
{
    /**
     * @var ReaderComposite
     */
    private $readerComposite;

    /**
     * @var SchemaBuilder
     */
    private $schemaBuilder;

    /**
     * @param ReaderComposite $readerComposite
     * @param SchemaBuilder $schemaBuilder
     */
    public function __construct(ReaderComposite $readerComposite, SchemaBuilder $schemaBuilder)
    {
        $this->readerComposite = $readerComposite;
        $this->schemaBuilder = $schemaBuilder;
    }

    /**
     * Convert data from different sources to object representation
     *
     * @param Schema $schema
     * @return Schema
     */
    public function parse(Schema $schema)
    {
        $data = $this->readerComposite->read();
        $this->schemaBuilder->addTablesData($data['table']);
        $schema = $this->schemaBuilder->build($schema);
        return $schema;
    }
}
