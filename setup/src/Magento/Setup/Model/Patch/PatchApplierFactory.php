<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

use Magento\Framework\ObjectManagerInterface;

/**
 * This factory allows to create data patches applier
 */
class PatchApplierFactory
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
     * Create new instance of patch applier
     *
     * @param array $arguments
     * @return PatchInterface
     */
    public function create($arguments = [])
    {
        return $this->objectManager->create(\Magento\Setup\Model\Patch\PatchApplier::class, $arguments);
    }
}
