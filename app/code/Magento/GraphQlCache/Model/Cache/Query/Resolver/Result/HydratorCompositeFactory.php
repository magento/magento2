<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\App\ObjectManager;

/**
 * Factory class for composite hydrator.
 */
class HydratorCompositeFactory {

    /**
     * Create composite hydrator instance with list of hydrator instances.
     *
     * @param array $hydratorsOrdered
     * @return HydratorInterface
     */
    public function create(array $hydratorsOrdered): HydratorInterface
    {
        $hydratorInstances = [];
        foreach ($hydratorsOrdered as $hydratorClass) {
            $hydratorInstances[] = ObjectManager::getInstance()->get($hydratorClass);
        }
        return ObjectManager::getInstance()->create(HydratorComposite::class, ['hydrators' => $hydratorInstances]);
    }
}
