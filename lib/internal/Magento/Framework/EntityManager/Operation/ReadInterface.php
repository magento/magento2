<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\OperationInterface;

/**
 * Interface for reading entity data
 * @since 2.1.0
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
     * @since 2.1.0
     */
    public function execute($entity, $identifier, $arguments = []);
}
