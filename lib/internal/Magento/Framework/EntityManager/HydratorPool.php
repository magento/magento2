<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class HydratorPool
 * @since 2.1.0
 */
class HydratorPool
{
    /**
     * @var HydratorInterface[]
     * @since 2.1.0
     */
    private $hydrators;

    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $hydrators
     * @since 2.1.0
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
     * @since 2.1.0
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
