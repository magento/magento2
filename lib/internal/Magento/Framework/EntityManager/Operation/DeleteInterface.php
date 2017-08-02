<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\OperationInterface;

/**
 * Interface for deleting entity
 * @since 2.1.0
 */
interface DeleteInterface extends OperationInterface
{
    /**
     * Delete entity
     *
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     * @since 2.1.0
     */
    public function execute($entity, $arguments = []);
}
