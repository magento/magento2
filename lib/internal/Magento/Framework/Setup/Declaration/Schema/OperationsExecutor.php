<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\DataSavior\DataSaviorInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementAggregatorFactory;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementFactory;
use Magento\Framework\Setup\Declaration\Schema\Diff\DiffInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;
use Magento\Framework\Setup\Declaration\Schema\Operations\AddColumn;
use Magento\Framework\Setup\Declaration\Schema\Operations\CreateTable;
use Magento\Framework\Setup\Declaration\Schema\Operations\ReCreateTable;

/**
 * Schema operations executor.
 *
 * Go through all available SQL operations and execute each one with data from change registry.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OperationsExecutor
{
    /**
     * Request keys.
     */
    const KEY_SAFE_MODE = 'safe-mode';
    const KEY_DATA_RESTORE = 'data-restore';

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
     * @var DataSaviorInterface[]
     */
    private $dataSaviorsCollection;

    /**
     * @var DryRunLogger
     */
    private $dryRunLogger;

    /**
     * Constructor.
     *
     * @param array $operations
     * @param array $dataSaviorsCollection
     * @param Sharding $sharding
     * @param ResourceConnection $resourceConnection
     * @param StatementFactory $statementFactory
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param StatementAggregatorFactory $statementAggregatorFactory
     * @param DryRunLogger $dryRunLogger
     */
    public function __construct(
        array $operations,
        array $dataSaviorsCollection,
        Sharding $sharding,
        ResourceConnection $resourceConnection,
        StatementFactory $statementFactory,
        DbSchemaWriterInterface $dbSchemaWriter,
        StatementAggregatorFactory $statementAggregatorFactory,
        DryRunLogger $dryRunLogger
    ) {
        $this->operations = $operations;
        $this->sharding = $sharding;
        $this->resourceConnection = $resourceConnection;
        $this->statementFactory = $statementFactory;
        $this->dbSchemaWriter = $dbSchemaWriter;
        $this->statementAggregatorFactory = $statementAggregatorFactory;
        $this->dataSaviorsCollection = $dataSaviorsCollection;
        $this->dryRunLogger = $dryRunLogger;
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
     * Check if during this operation we need to restore data
     *
     * @param OperationInterface $operation
     * @return bool
     */
    private function operationIsOppositeToDestructive(OperationInterface $operation)
    {
        return $operation instanceof AddColumn ||
            $operation instanceof CreateTable ||
            $operation instanceof ReCreateTable;
    }

    /**
     * Loop through all operations that are configured in di.xml
     * and execute them with elements from Diff.
     *
     * @see    OperationInterface
     * @param  DiffInterface $diff
     * @param array $requestData
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(DiffInterface $diff, array $requestData)
    {
        $this->startSetupForAllConnections();
        $tableHistories = $diff->getAll();
        $dryRun = isset($requestData[DryRunLogger::INPUT_KEY_DRY_RUN_MODE]) &&
            $requestData[DryRunLogger::INPUT_KEY_DRY_RUN_MODE];

        if ($dryRun) {
            $this->dryRunLogger->prepareToDryRun();
        }

        if (is_array($tableHistories)) {
            foreach ($tableHistories as $tableHistory) {
                $destructiveElements = [];
                $oppositeToDestructiveElements = [];
                $statementAggregator = $this->statementAggregatorFactory->create();

                foreach ($this->operations as $operation) {
                    if (isset($tableHistory[$operation->getOperationName()])) {
                        /** @var ElementHistory $elementHistory */
                        foreach ($tableHistory[$operation->getOperationName()] as $elementHistory) {
                            $statementAggregator->addStatements($operation->doOperation($elementHistory));

                            if ($operation->isOperationDestructive()) {
                                $destructiveElements[] = $elementHistory->getOld();
                            } elseif ($this->operationIsOppositeToDestructive($operation)) {
                                $oppositeToDestructiveElements[] = $elementHistory->getNew();
                            }
                        }
                    }
                }

                $this->doDump($destructiveElements, $requestData);
                $this->dbSchemaWriter->compile($statementAggregator, $dryRun);
                $this->doRestore($oppositeToDestructiveElements, $requestData);
            }
        }

        $this->endSetupForAllConnections();
    }

    /**
     * Do restore of destructive operations
     *
     * @param array $elements
     * @param array $requestData
     */
    private function doRestore(array $elements, array $requestData)
    {
        $restoreMode = isset($requestData[self::KEY_DATA_RESTORE]) && $requestData[self::KEY_DATA_RESTORE];

        if ($restoreMode) {
            /**
             * @var ElementInterface $element
             */
            foreach ($elements as $element) {
                foreach ($this->dataSaviorsCollection as $dataSavior) {
                    if ($dataSavior->isAcceptable($element)) {
                        $dataSavior->restore($element);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Do dump of destructive operations
     *
     * @param array $elements
     * @param array $requestData
     */
    private function doDump(array $elements, array $requestData)
    {
        $safeMode = isset($requestData[self::KEY_SAFE_MODE]) && $requestData[self::KEY_SAFE_MODE];

        if ($safeMode) {
            /**
             * @var ElementInterface $element
             */
            foreach ($elements as $element) {
                foreach ($this->dataSaviorsCollection as $dataSavior) {
                    if ($dataSavior->isAcceptable($element)) {
                        $dataSavior->dump($element);
                        break;
                    }
                }
            }
        }
    }
}
