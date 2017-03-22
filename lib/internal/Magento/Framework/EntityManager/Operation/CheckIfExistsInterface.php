<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
