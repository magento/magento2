<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel;

/**
 * Pool of resource model instances per entity
 *
 * @api
 */
interface ResourceModelPoolInterface
{
    /**
     * Return instance for given class name from pool.
     *
     * @param string $resourceClassName
     * @return AbstractResource
     */
    public function get(string $resourceClassName): AbstractResource;
}
