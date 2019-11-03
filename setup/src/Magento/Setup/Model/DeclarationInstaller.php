<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff;
use Magento\Framework\Setup\Declaration\Schema\OperationsExecutor;
use Magento\Framework\Setup\Declaration\Schema\SchemaConfigInterface;

/**
 * Declaration Installer is facade for installation and upgrade db in declaration mode.
 */
class DeclarationInstaller
{
    /**
     * @var OperationsExecutor
     */
    private $operationsExecutor;

    /**
     * @var SchemaDiff
     */
    private $schemaDiff;

    /**
     * @var SchemaConfigInterface
     */
    private $schemaConfig;

    /**
     * Constructor.
     *
     * @param SchemaConfigInterface $schemaConfig
     * @param SchemaDiff $schemaDiff
     * @param OperationsExecutor $operationsExecutor
     */
    public function __construct(
        SchemaConfigInterface $schemaConfig,
        SchemaDiff $schemaDiff,
        OperationsExecutor $operationsExecutor
    ) {
        $this->operationsExecutor = $operationsExecutor;
        $this->schemaConfig = $schemaConfig;
        $this->schemaDiff = $schemaDiff;
    }

    /**
     * Install Schema in declarative way.
     *
     * @param array $requestData -> Data params which comes from UI or from CLI.
     * @return void
     */
    public function installSchema(array $requestData)
    {
        $declarativeSchema = $this->schemaConfig->getDeclarationConfig();
        $dbSchema = $this->schemaConfig->getDbConfig();
        $diff = $this->schemaDiff->diff($declarativeSchema, $dbSchema);
        $this->operationsExecutor->execute($diff, $requestData);
    }
}
