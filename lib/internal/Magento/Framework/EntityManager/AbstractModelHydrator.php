<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

use Magento\Framework\Model\AbstractModel;

/**
 * Class AbstractModelHydrator
 * @since 2.1.0
 */
class AbstractModelHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function extract($entity)
    {
        return $entity->getData();
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function hydrate($entity, array $data)
    {
        $entity->setData(array_merge($entity->getData(), $data));
        return $entity;
    }
}
