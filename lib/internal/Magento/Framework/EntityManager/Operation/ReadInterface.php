<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\OperationInterface;

/**
 * Interface for reading entity data
 * @deprecated
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
