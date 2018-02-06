<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

use Magento\Framework\ObjectManagerInterface;

/**
 * This factory allows to create schema patches:
 * @see SchemaPatchInterface
 */
class SchemaPatchFactory
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
     * Create new instance of
     * @param string $instanceName
     * @return SchemaPatchInterface
     */
    public function create($instanceName)
    {
        $patchInstance = $this->objectManager->create($instanceName, []);
        if (!$patchInstance instanceof SchemaPatchInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    "%s should implement %s interface",
                    $instanceName,
                    SchemaPatchInterface::class
                )
            );
        }
        return $patchInstance;
    }
}
