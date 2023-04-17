<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for composite hydrator.
 */
class HydratorCompositeFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

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
            $hydratorInstances[] = $this->objectManager->get($hydratorClass);
        }
        return $this->objectManager->create(HydratorComposite::class, ['hydrators' => $hydratorInstances]);
    }
}
