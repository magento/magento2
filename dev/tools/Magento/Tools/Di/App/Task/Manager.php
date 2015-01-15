<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\App\Task;

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
    public function __construct(OperationFactory $operationFactory)
    {
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
     * @return void
     */
    public function process()
    {
        /** @var OperationInterface $operation */
        foreach ($this->operationsList as $operation) {
            $operation->doOperation();
        }
        $this->operationsList = [];
    }
}
