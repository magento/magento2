<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\OperationInterface;

/**
 * Interface for creating entity
 * @deprecated
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
