<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Framework\EntityManager\OperationInterface;
use Magento\Framework\EntityManager\Operation\CheckIfExists;
use Magento\Framework\EntityManager\Operation\Read;
use Magento\Framework\EntityManager\Operation\Create;
use Magento\Framework\EntityManager\Operation\Update;
use Magento\Framework\EntityManager\Operation\Delete;

/**
 * Class OperationPool
 */
class OperationPool
{
    /**
     * @var array
     */
    private $defaultOperations = [
        'checkIfExists' => CheckIfExists::class,
        'read' => Read::class,
        'create' => Create::class,
        'update' => Update::class,
        'delete' => Delete::class,
    ];

    /**
     * @var array
     */
    private $operations;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * OperationPool constructor.
     * @param ObjectManager $objectManager
     * @param string[] $operations
     */
    public function __construct(
        ObjectManager $objectManager,
        $operations = []
    ) {
        $this->objectManager = $objectManager;
        $this->operations = array_replace_recursive(
            ['default' => $this->defaultOperations],
            $operations
        );
    }

    /**
     * Returns operation by name by entity type
     *
     * @param string $entityType
     * @param string $operationName
     * @return OperationInterface
     */
    public function getOperation($entityType, $operationName)
    {
        if (!isset($this->operations[$entityType][$operationName])) {
            return $this->objectManager->get($this->operations['default'][$operationName]);
        }
        return $this->objectManager->get($this->operations[$entityType][$operationName]);
    }
}
