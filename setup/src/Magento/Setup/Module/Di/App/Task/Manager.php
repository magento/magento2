<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task;

class Manager
{
    /**
     * @var OperationFactory
     */
    private $operationFactory;

    /**
     * @var OperationInterface[]
     */
    private $operationsList = [];

    /**
     * @param OperationFactory $operationFactory
     */
    public function __construct(
        OperationFactory $operationFactory
    ) {
        $this->operationFactory = $operationFactory;
    }

    /**
     * Adds operations to task
     *
     * @param string $operationCode
     * @param mixed $arguments
     * @return void
     */
    public function addOperation($operationCode, $arguments = null)
    {
        $this->operationsList[] = $this->operationFactory->create($operationCode, $arguments);
    }

    /**
     * Processes list of operations
     *
     * @param callable $beforeCallback
     * @param callable $afterCallback
     * @return void
     */
    public function process(\Closure $beforeCallback = null, \Closure $afterCallback = null)
    {
        /** @var OperationInterface $operation */
        foreach ($this->operationsList as $operation) {
            if (is_callable($beforeCallback)) {
                $beforeCallback($operation);
            }

            $operation->doOperation();

            if (is_callable($afterCallback)) {
                $afterCallback($operation);
            }
        }
        $this->operationsList = [];
    }
}
