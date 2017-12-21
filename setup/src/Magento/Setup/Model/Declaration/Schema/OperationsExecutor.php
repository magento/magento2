<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Diff\DiffInterface;

/**
 * Go through all available SQL operations and do execute of each of them
 * with data that come from change registry
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
     * @param array $operations
     * @param Sharding $sharding
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        array $operations,
        Sharding $sharding,
        ResourceConnection $resourceConnection
    ) {
        $this->operations = $operations;
        $this->sharding = $sharding;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * In order to successfully run all operations we need to start setup for all
     * connections first
     *
     * @return void
     */
    private function startSetupForAllConnections()
    {
        foreach ($this->sharding->getResources() as $resource) {
            $this->resourceConnection->getConnection($resource)
                ->startSetup();
        }
    }

    /**
     * In order to revert previous state we need to end setup for all connections
     * connections first
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
     * and execute them with elements from ChangeRegistyr
     *
     * @see OperationInterface
     * @param DiffInterface $diff
     * @return void
     */
    public function execute(DiffInterface $diff)
    {
        $this->startSetupForAllConnections();

        foreach ($this->operations as $operation) {
            $histories = $diff->get($operation->getOperationName());

            foreach ($histories as $history) {
                $operation->doOperation($history);
            }
        }

        $this->endSetupForAllConnections();
    }
}
