<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Class EntityHydrator
 */
class EntityHydrator implements HydratorInterface
{
    /**
     * @param object $entity
     * @return array
     */
    public function extract($entity)
    {
        //TODO: refactor with using API data interfaces
        return $entity->getData();
    }

    /**
     * @param object $entity
     * @param array $data
     * @return object
     */
    public function hydrate($entity, array $data)
    {
        //TODO: refactor with using API data interfaces
        $entity->setData(array_merge($entity->getData(), $data));
        return $entity;
    }
}
