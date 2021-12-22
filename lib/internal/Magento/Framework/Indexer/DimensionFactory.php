<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

use Magento\Framework\ObjectManagerInterface;

/**
 * Dimension Factory
 *
 * @api
 * @since 101.0.6
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
     * @param string $name
     * @param string $value
     * @return Dimension
     * @since 101.0.6
     */
    public function create(string $name, string $value): Dimension
    {
        return $this->objectManager->create(
            Dimension::class,
            [
                'name' => $name,
                'value' => $value,
            ]
        );
    }
}
