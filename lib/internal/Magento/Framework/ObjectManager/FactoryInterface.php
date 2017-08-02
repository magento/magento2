<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

use Magento\Framework\ObjectManagerInterface;

/**
 * Interface \Magento\Framework\ObjectManager\FactoryInterface
 *
 * @since 2.0.0
 */
interface FactoryInterface
{
    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws \LogicException
     * @throws \BadMethodCallException
     * @since 2.0.0
     */
    public function create($requestedType, array $arguments = []);

    /**
     * Set object manager
     *
     * @param ObjectManagerInterface $objectManager
     *
     * @return void
     * @since 2.0.0
     */
    public function setObjectManager(ObjectManagerInterface $objectManager);
}
