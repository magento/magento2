<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class HydratorPool
 */
class HydratorPool
{
    /**
     * @var HydratorInterface[]
     */
    private $hydrators;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $hydrators
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $hydrators = []
    ) {
        $this->objectManager = $objectManager;
        $this->hydrators = $hydrators;
    }

    /**
     * @param string $entityType
     * @return HydratorInterface
     */
    public function getHydrator($entityType)
    {
        if (isset($this->hydrators[$entityType])) {
            return $this->objectManager->get($this->hydrators[$entityType]);
        } else {
            return $this->objectManager->get(HydratorInterface::class);
        }
    }
}
