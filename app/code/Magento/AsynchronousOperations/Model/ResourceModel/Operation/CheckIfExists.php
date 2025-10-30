<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Magento\Framework\EntityManager\Operation\CheckIfExistsInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * CheckIfExists operation for list of bulk operations.
 */
class CheckIfExists implements CheckIfExistsInterface
{
    /**
     * Always returns false because all operations will be saved using insertOnDuplicate query.
     *
     * @param object $entity
     * @param array $arguments
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        return false;
    }
}
