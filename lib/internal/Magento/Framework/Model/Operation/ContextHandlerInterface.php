<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Operation;

use Magento\Framework\Model\Entity\EntityMetadata;

/**
 * Interface ContextHandler
 *
 * Retrieve context value map based on metadata context
 */
interface ContextHandlerInterface
{
    /**
     * @param EntityMetadata $metadata
     * @param array $entityData
     * @return array
     */
    public function retrieve(EntityMetadata $metadata, array $entityData);
}
