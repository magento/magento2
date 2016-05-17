<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

/**
 * Interface HydratorInterface
 */
interface HydratorInterface
{
    /**
     * Extract data from object
     *
     * @param object $entity
     * @return array
     */
    public function extract($entity);

    /**
     * Populate entity with data
     *
     * @param object $entity
     * @param array $data
     * @return object
     */
    public function hydrate($entity, array $data);
}
