<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Framework\EntityManager\OperationInterface;

/**
 * Class OperationPool
 * @since 2.1.0
 */
class OperationPool
{
    /**
     * @var array
     * @since 2.1.0
     */
    private $operations;

    /**
     * @var ObjectManager
     * @since 2.1.0
     */
    private $objectManager;

    /**
     * OperationPool constructor.
     * @param ObjectManager $objectManager
     * @param string[] $operations
     * @since 2.1.0
     */
    public function __construct(
        ObjectManager $objectManager,
        $operations
    ) {
        $this->objectManager = $objectManager;
        $this->operations = $operations;
    }

    /**
     * Returns operation by name by entity type
     *
     * @param string $entityType
     * @param string $operationName
     * @return OperationInterface
     * @since 2.1.0
     */
    public function getOperation($entityType, $operationName)
    {
        if (!isset($this->operations[$entityType][$operationName])) {
            return $this->objectManager->get($this->operations['default'][$operationName]);
        }
        return $this->objectManager->get($this->operations[$entityType][$operationName]);
    }
}
