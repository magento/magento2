<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\StatementAggregatorFactory;
use Magento\Setup\Model\Declaration\Schema\Db\StatementFactory;
use Magento\Setup\Model\Declaration\Schema\Diff\DiffInterface;

/**
 * Schema operations executor.
 *
 * Go through all available SQL operations and execute each one with data from change registry.
 */
class OperationsExecutor
{
    /**
     * @var OperationInterface[]
     */
    private $operations;

    /**
     * @var Sharding
     */
    private $sharding;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StatementFactory
     */
    private $statementFactory;

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * @var StatementAggregatorFactory
     */
    private $statementAggregatorFactory;

    /**
     * Constructor.
     *
     * @param array $operations
     * @param Sharding $sharding
     * @param ResourceConnection $resourceConnection
     * @param StatementFactory $statementFactory
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param StatementAggregatorFactory $statementAggregatorFactory
     */
    public function __construct(
        array $operations,
        Sharding $sharding,
        ResourceConnection $resourceConnection,
        StatementFactory $statementFactory,
        DbSchemaWriterInterface $dbSchemaWriter,
        StatementAggregatorFactory $statementAggregatorFactory
    ) {
        $this->operations = $operations;
        $this->sharding = $sharding;
        $this->resourceConnection = $resourceConnection;
        $this->statementFactory = $statementFactory;
        $this->dbSchemaWriter = $dbSchemaWriter;
        $this->statementAggregatorFactory = $statementAggregatorFactory;
    }

    /**
     * Retrieve only destructive operation names.
     *
     * For example, drop_table, recreate_table, etc.
     *
     * @return array
     */
    public function getDestructiveOperations()
    {
        $operations = [];

        foreach ($this->operations as $operation) {
            if ($operation->isOperationDestructive()) {
                $operations[$operation->getOperationName()] = $operation->getOperationName();
            }
        }

        return $operations;
    }

    /**
     * In order to successfully run all operations we need to start setup for all
     * connections first.
     *
     * @return void
     */
    private function startSetupForAllConnections()
    {
        foreach ($this->sharding->getResources() as $resource) {
            $this->resourceConnection->getConnection($resource)
                ->startSetup();
            $this->resourceConnection->getConnection($resource)
                ->query('SET UNIQUE_CHECKS=0');
        }
    }

    /**
     * In order to revert previous state we need to end setup for all connections
     * connections first.
     *
     * @return void
     */
    private function endSetupForAllConnections()
    {
        foreach ($this->sharding->getResources() as $resource) {
            $this->resourceConnection->getConnection($resource)
                ->endSetup();
        }
    }

    /**
     * Loop through all operations that are configured in di.xml
     * and execute them with elements from ChangeRegistry.
     *
     * @see    OperationInterface
     * @param  DiffInterface $diff
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(DiffInterface $diff)
    {
        $this->startSetupForAllConnections();
        $tableHistories = $diff->getAll();
        if (is_array($tableHistories)) {
            foreach ($tableHistories as $tableHistory) {
                $statementAggregator = $this->statementAggregatorFactory->create();

                foreach ($this->operations as $operation) {
                    if (isset($tableHistory[$operation->getOperationName()])) {
                        /** @var ElementHistory $elementHistory */
                        foreach ($tableHistory[$operation->getOperationName()] as $elementHistory) {
                            $statementAggregator->addStatements(
                                $operation->doOperation($elementHistory)
                            );
                        }
                    }
                }
                $this->dbSchemaWriter->compile($statementAggregator);
            }
        }

        $this->endSetupForAllConnections();
    }
}
