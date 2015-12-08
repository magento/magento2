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
     * @param array $operations
     */
    public function __construct(
        $operations
    ) {
        $this->operations = $operations;
    }

    /**
     * @param string $entityType
     * @param string $operationName
     * @return Operation\WriteInterface
     * @throws \Exception
     */
    public function getWriteOperation($entityType, $operationName)
    {
        if (!isset($this->operations[$entityType][$operationName])
            || !$this->operations[$entityType][$operationName] instanceof Operation\WriteInterface
        ) {
            return $this->operations['default'][$operationName];
        }
        return $this->operations[$entityType][$operationName];
    }

    /**
     * @param string $entityType
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
        }
        return $this->operations[$entityType]['read'];
    }
}
