<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\OperationInterface;

/**
 * Interface for checking if entity exists
 */
interface CheckIfExistsInterface extends OperationInterface
{
    /**
     * Check if entity exists
     *
     * @param object $entity
     * @return bool
     * @throws \Exception
     */
    public function execute($entity);
}
