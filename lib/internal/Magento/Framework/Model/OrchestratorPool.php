<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;

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
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * OrchestratorPool constructor.
     * @param ObjectManager $objectManager
     * @param string[] $operations
     */
    public function __construct(
        ObjectManager $objectManager,
        $operations
    ) {
        $this->objectManager = $objectManager;
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
        if (!isset($this->operations[$entityType][$operationName])) {
            return $this->objectManager->get($this->operations['default'][$operationName]);
        }
        return $this->objectManager->get($this->operations[$entityType][$operationName]);
    }

    /**
     * @param string $entityType
     * @return Operation\ReadInterface
     * @throws \Exception
     */
    public function getReadOperation($entityType)
    {
        if (!isset($this->operations[$entityType]['read'])) {
            return $this->objectManager->get($this->operations['default']['read']);
        }
        return $this->objectManager->get($this->operations[$entityType]['read']);
    }
}
