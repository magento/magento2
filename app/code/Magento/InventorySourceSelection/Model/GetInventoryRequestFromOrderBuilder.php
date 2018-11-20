<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model;

use Magento\InventorySourceSelection\Exception\UndefinedInventoryRequestBuilderException;

class GetInventoryRequestFromOrderBuilder
{
    /**
     * @var InventoryRequestFromOrderBuilderInterface[]
     */
    private $buildersByAlgorithm;

    /**
     * GetInventoryRequestBuilder constructor.
     *
     * @param array $buildersByAlgorithm
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        array $buildersByAlgorithm = []
    ) {
        $this->buildersByAlgorithm = $buildersByAlgorithm;

        foreach ($this->buildersByAlgorithm as $code => $builder) {
            if (!($builder instanceof InventoryRequestFromOrderBuilderInterface)) {
                throw new \InvalidArgumentException(
                    'Builder ' . $code . ' must implement InventoryRequestBuilderFromOrderInterface'
                );
            }
        }
    }

    /**
     * Get a builder from one algorithm
     *
     * @param string $algorithm
     * @return InventoryRequestFromOrderBuilderInterface
     * @throws UndefinedInventoryRequestBuilderException
     */
    public function execute(string $algorithm): InventoryRequestFromOrderBuilderInterface
    {
        if (!isset($this->buildersByAlgorithm[$algorithm])) {
            throw new UndefinedInventoryRequestBuilderException(
                __('No request builder is defined for algorithm %1', $algorithm)
            );
        }

        return $this->buildersByAlgorithm[$algorithm];
    }
}
