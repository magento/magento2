<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ObjectManager;

use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FactoryStub implements FactoryInterface
{
    /**
     * @param ConfigInterface $config
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param DefinitionInterface $definitions
     * @param array $globalArguments
     * @throws \BadMethodCallException
     */
    public function __construct($config, $objectManager = null, $definitions = null, $globalArguments = [])
    {
        throw new \BadMethodCallException(__METHOD__);
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws \BadMethodCallException
     */
    public function create($requestedType, array $arguments = [])
    {
        throw new \BadMethodCallException(__METHOD__);
    }

    /**
     * Set object manager
     *
     * @param ObjectManagerInterface $objectManager
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setObjectManager(ObjectManagerInterface $objectManager)
    {
    }
}
