<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Setup\Model\Declaration\Schema\ChangeRegistryFactory;
use Magento\Setup\Model\Declaration\Schema\Declaration\Parser;
use Magento\Setup\Model\Declaration\Schema\Diff\SchemaDiff;
use Magento\Setup\Model\Declaration\Schema\Dto\SchemaFactory;
use Magento\Setup\Model\Declaration\Schema\OperationsExecutor;
use Magento\Setup\Model\Declaration\Schema\RequestFactory;
use Magento\Setup\Model\Declaration\Schema\SchemaParserInterface;

/**
 * Declaration Installer is facade for installation and upgrade db in declaration mode
 */
class DeclarationInstaller
{
    /**
     * @var Parser
     */
    private $declarationParser;

    /**
     * @var SchemaFactory
     */
    private $structureFactory;

    /**
     * @var OperationsExecutor
     */
    private $operationsExecutor;

    /**
     * @var SchemaParserInterface
     */
    private $generatedParser;

    /**
     * @var SchemaDiff
     */
    private $schemaDiff;

    /**
     * @var ChangeRegistryFactory
     */
    private $changeRegistryFactory;

    /**
     * @var RequestFactory
     */
    private $requestFactory;
    /**
     * @var Parser
     */
    private $declarativeParser;

    /**
     * @param SchemaFactory $structureFactory
     * @param Declaration\Schema\Db\Parser|SchemaParserInterface $generatedParser
     * @param Parser $declarativeParser
     * @param ChangeRegistryFactory $changeRegistryFactory
     * @param SchemaDiff $structureDiff
     * @param OperationsExecutor $operationsExecutor
     * @param RequestFactory $requestFactory
     */
    public function __construct(
        SchemaFactory $structureFactory,
        \Magento\Setup\Model\Declaration\Schema\Db\Parser $generatedParser,
        \Magento\Setup\Model\Declaration\Schema\Declaration\Parser $declarativeParser,
        ChangeRegistryFactory $changeRegistryFactory,
        SchemaDiff $structureDiff,
        OperationsExecutor $operationsExecutor,
        RequestFactory $requestFactory
    ) {
        $this->declarationParser = $declarativeParser;
        $this->structureFactory = $structureFactory;
        $this->operationsExecutor = $operationsExecutor;
        $this->generatedParser = $generatedParser;
        $this->schemaDiff = $structureDiff;
        $this->changeRegistryFactory = $changeRegistryFactory;
        $this->requestFactory = $requestFactory;
        $this->declarativeParser = $declarativeParser;
    }

    /**
     * Install Schema in declarative way
     *
     * @param array $requestData -> Data params which comes from UI or from CLI
     * @return void
     */
    public function installSchema(array $requestData)
    {
        $changeRegistry = $this->changeRegistryFactory->create();
        $schema = $this->structureFactory->create();
        $generatedStructure = $this->structureFactory->create();
        $this->declarationParser->parse($schema);
        $this->generatedParser->parse($generatedStructure);
        $this->schemaDiff->diff($schema, $generatedStructure, $changeRegistry);
        $changeRegistry->registerSchema($schema);
        $changeRegistry->registerInstallationRequest(
            $this->requestFactory->create($requestData)
        );
        $this->operationsExecutor->execute($changeRegistry);
    }
}
