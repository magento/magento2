<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

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
     * @param array $operations
     */
    public function __construct(array $operations)
    {
        $this->operations = $operations;
    }

    /**
     * Loop through all operations that are configured in di.xml
     * and execute them with elements from ChangeRegistyr
     *
     * @see OperationInterface
     * @param ChangeRegistryInterface $changeRegistry
     * @return void
     */
    public function execute(ChangeRegistryInterface $changeRegistry)
    {
        foreach ($this->operations as $operation) {
            $histories = $changeRegistry->get($operation->getOperationName());

            foreach ($histories as $history) {
                $operation->doOperation($history);
            }
        }
    }
}
