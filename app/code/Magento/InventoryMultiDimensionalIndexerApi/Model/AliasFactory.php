<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMultiDimensionalIndexerApi\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Alias Factory
 *
 * @api
 */
class AliasFactory
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
     * @param array $arguments
     * @return Alias
     */
    public function create(array $arguments = []): Alias
    {
        return $this->objectManager->create(Alias::class, $arguments);
    }
}
