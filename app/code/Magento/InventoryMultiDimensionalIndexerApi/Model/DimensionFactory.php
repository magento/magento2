<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMultiDimensionalIndexerApi\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Dimension Factory
 *
 * @api
 */
class DimensionFactory
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
     * @return Dimension
     */
    public function create(array $arguments = []): Dimension
    {
        return $this->objectManager->create(Dimension::class, $arguments);
    }
}
