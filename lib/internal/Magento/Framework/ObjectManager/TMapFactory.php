<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class TMapFactory
 * @since 2.1.0
 */
class TMapFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    private $objectManager;

    /**
     * TMapFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @since 2.1.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $args
     * @return TMap
     * @since 2.1.0
     */
    public function create(array $args)
    {
        return $this->objectManager->create(TMap::class, $args);
    }

    /**
     * @param array $args
     * @return TMap
     * @since 2.1.0
     */
    public function createSharedObjectsMap(array $args)
    {
        return $this->objectManager->create(
            TMap::class,
            array_merge(
                $args,
                [
                    'objectCreationStrategy' => function (ObjectManagerInterface $om, $objectName) {
                        return $om->get($objectName);
                    }
                ]
            )
        );
    }
}
