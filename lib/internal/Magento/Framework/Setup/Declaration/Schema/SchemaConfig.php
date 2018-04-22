<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\Config\FileResolverByModule;
use Magento\Framework\Setup\Declaration\Schema\Db\SchemaBuilder as DbSchemaBuilder;
use Magento\Framework\Setup\Declaration\Schema\Declaration\SchemaBuilder as DeclarativeSchemaBuilder;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ReaderComposite;
use Magento\Framework\Setup\Declaration\Schema\Dto\SchemaFactory;

/**
 * {@inheritdoc}
 */
class SchemaConfig implements SchemaConfigInterface
{
    /**
     * @var DbSchemaBuilder
     */
    private $dbSchemaBuilder;

    /**
     * @var DeclarativeSchemaBuilder
     */
    private $declarativeSchemaBuilder;

    /**
     * @var SchemaFactory
     */
    private $schemaFactory;

    /**
     * @var ReaderComposite
     */
    private $readerComposite;

    /**
     * Constructor.
     *
     * @param DbSchemaBuilder          $dbSchemaBuilder
     * @param DeclarativeSchemaBuilder $declarativeSchemaBuilder
     * @param SchemaFactory            $schemaFactory
     * @param ReaderComposite          $readerComposite
     */
    public function __construct(
        DbSchemaBuilder $dbSchemaBuilder,
        DeclarativeSchemaBuilder $declarativeSchemaBuilder,
        SchemaFactory $schemaFactory,
        ReaderComposite $readerComposite
    ) {
        $this->dbSchemaBuilder = $dbSchemaBuilder;
        $this->declarativeSchemaBuilder = $declarativeSchemaBuilder;
        $this->schemaFactory = $schemaFactory;
        $this->readerComposite = $readerComposite;
    }

    /**
     * @inheritdoc
     */
    public function getDbConfig()
    {
        $schema = $this->schemaFactory->create();
        $schema = $this->dbSchemaBuilder->build($schema);
        return $schema;
    }

    /**
     * @inheritdoc
     */
    public function getDeclarationConfig()
    {
        $schema = $this->schemaFactory->create();
        $data = $this->readerComposite->read(FileResolverByModule::ALL_MODULES);
        $this->declarativeSchemaBuilder->addTablesData($data['table']);
        $schema = $this->declarativeSchemaBuilder->build($schema);
        return $schema;
    }
}
