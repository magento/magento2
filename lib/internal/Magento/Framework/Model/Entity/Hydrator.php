<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Entity;

use Magento\Framework\EntityManager\EntityHydratorInterface;

/**
 * Class Hydrator
 */
class Hydrator implements EntityHydratorInterface
{
    /**
     * @param object $entity
     * @return array
     */
    public function extract($entity)
    {
        return $entity->getData();
    }

    /**
     * @param object $entity
     * @param array $data
     * @return object
     */
    public function hydrate($entity, array $data)
    {
        $entity->setData(array_merge($entity->getData(), $data));
        return $entity;
    }
}
