<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

/**
 * Class Orchestrator
 */
class OrchestratorPool
{
    /**
     * @var array
     */
    protected $operations;


    /**
     * @param $operations
     */
    public function __construct(
        $operations
    ) {
        $this->operations = $operations;
    }

    /**
     * @param $entityType
     * @param $operationName
     * @return Operation\WriteInterface
     * @throws \Exception
     */
    public function getWriteOperation($entityType, $operationName)
    {
        if (!isset($this->operations[$entityType][$operationName])
            || !$this->operations[$entityType][$operationName] instanceof Operation\WriteInterface
        ) {
            return $this->operations['default'][$operationName];
//            throw new \Exception('Requested operation is\'t implemented yet');
        }
        return $this->operations[$entityType][$operationName];
    }

    /**
     * @param $entityType
     * @return Operation\ReadInterface
     * @throws \Exception
     */
    public function getReadOperation($entityType)
    {
        //TODO: remove interfaces Read and Write
        if (!isset($this->operations[$entityType]['read'])
            || !$this->operations[$entityType]['read'] instanceof Operation\ReadInterface
        ) {
            return $this->operations['default']['read'];
//            throw new \Exception('Requested operation doesn\'t implemented yet');
        }
        return $this->operations[$entityType]['read'];
    }
}
