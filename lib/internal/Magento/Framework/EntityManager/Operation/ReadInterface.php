<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\OperationInterface;

/**
 * Interface for reading entity data
 */
interface ReadInterface extends OperationInterface
{
    /**
     * Read data and populate entity
     *
     * @param object $entity
     * @param string $identifier
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $identifier, $arguments = []);
}
