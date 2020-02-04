<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Patch;

use Magento\Framework\ObjectManagerInterface;

/**
 * This factory allows to create data patches:
 * @see PatchInterface
 */
class PatchFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new instance of patch
     *
     * @param string $instanceName
     * @param array $arguments
     * @return PatchInterface
     */
    public function create($instanceName, $arguments = [])
    {
        $patchInstance = $this->objectManager->create('\\' . $instanceName, $arguments);
        if (!$patchInstance instanceof PatchInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    "%s should implement %s interface",
                    $instanceName,
                    PatchInterface::class
                )
            );
        }

        return $patchInstance;
    }
}
