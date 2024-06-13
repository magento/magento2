<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\OperationInterface;

/**
 * Interface for creating entity
 */
interface CreateInterface extends OperationInterface
{
    /**
     * Create entity
     *
     * @param object $entity
     * @param array $arguments
     * @return bool
     * @throws \Exception
     */
    public function execute($entity, $arguments = []);
}
