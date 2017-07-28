<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task;

/**
 * Class \Magento\Setup\Module\Di\App\Task\Manager
 *
 * @since 2.0.0
 */
class Manager
{
    /**
     * @var OperationFactory
     * @since 2.0.0
     */
    private $operationFactory;

    /**
     * @var OperationInterface[]
     * @since 2.0.0
     */
    private $operationsList = [];

    /**
     * @param OperationFactory $operationFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
