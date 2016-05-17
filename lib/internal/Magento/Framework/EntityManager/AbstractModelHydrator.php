<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

use Magento\Framework\Model\AbstractModel;

/**
 * Class AbstractModelHydrator
 */
class AbstractModelHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract($entity)
    {
        return $entity->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($entity, array $data)
    {
        $entity->setData(array_merge($entity->getData(), $data));
        return $entity;
    }
}
