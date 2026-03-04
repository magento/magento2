<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\EntityManager;

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
