<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

/**
 * Class MapperFactory
 * @package Magento\Framework\DB
 * @since 2.0.0
 */
class MapperFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create Mapper object
     *
     * @param string $className
     * @param array $arguments
     * @return MapperInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function create($className, array $arguments = [])
    {
        $mapper = $this->objectManager->create($className, $arguments);
        if (!$mapper instanceof MapperInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(
                    '%1 doesn\'t implement \Magento\Framework\DB\MapperInterface',
                    [$className]
                )
            );
        }
        return $mapper;
    }
}
