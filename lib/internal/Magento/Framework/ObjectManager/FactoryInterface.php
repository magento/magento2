<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

use Magento\Framework\ObjectManagerInterface;

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
     */
    public function create($requestedType, array $arguments = []);

    /**
     * Set object manager
     *
     * @param ObjectManagerInterface $objectManager
     *
     * @return void
     */
    public function setObjectManager(ObjectManagerInterface $objectManager);
}
