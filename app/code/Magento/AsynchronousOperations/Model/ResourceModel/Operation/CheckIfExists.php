<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
