<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class TMapFactory
 */
class TMapFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * TMapFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $args
     * @return TMap
     */
    public function create(array $args)
    {
        return $this->objectManager->create(TMap::class, $args);
    }

    /**
     * @param array $args
     * @return TMap
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
