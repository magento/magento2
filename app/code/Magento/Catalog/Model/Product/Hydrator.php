<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Framework\EntityManager\HydratorInterface;

/**
 * Class is used to extract data and populate entity with data
 */
class Hydrator implements HydratorInterface
{
    /**
     * @inheritdoc
     */
    public function extract($entity)
    {
        return $entity->getData();
    }

    /**
     * @inheritdoc
     */
    public function hydrate($entity, array $data)
    {
        $lockedAttributes = $entity->getLockedAttributes();
        $entity->unlockAttributes();
        $entity->setData(array_merge($entity->getData(), $data));
        foreach ($lockedAttributes as $attribute) {
            $entity->lockAttribute($attribute);
        }

        return $entity;
    }
}
