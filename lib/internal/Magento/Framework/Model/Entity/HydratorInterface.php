<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Interface HydratorInterface
 */
interface HydratorInterface
{
    /**
     * @param object $entity
     * @return array
     */
    public function extract($entity);

    /**
     * @param object $entity
     * @param array $data
     * @return object
     */
    public function hydrate($entity, array $data);
}