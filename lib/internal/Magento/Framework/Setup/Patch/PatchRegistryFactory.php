<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Setup\Patch;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create instance of patch registry
 */
class PatchRegistryFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $instanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = PatchRegistry::class
    ) {

        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @return PatchRegistry
     */
    public function create()
    {
        return $this->objectManager->create($this->instanceName);
    }
}
