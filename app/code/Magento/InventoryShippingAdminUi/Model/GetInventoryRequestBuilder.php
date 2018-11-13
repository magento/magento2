<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Get an inventory request builder
 */
class GetInventoryRequestBuilder
{
    /**
     * @var array
     */
    private $inventoryRequestBuilders;

    /**
     * GetInventoryRequestBuilder constructor.
     *
     * @param array $inventoryRequestBuilders
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        array $inventoryRequestBuilders = []
    ) {
        $this->inventoryRequestBuilders = $inventoryRequestBuilders;

        foreach ($this->inventoryRequestBuilders as $code => $builder) {
            if (!($builder instanceof InventoryRequestBuilderFromOrderInterface)) {
                throw new \InvalidArgumentException(
                    'Distance provider ' . $code . ' must implement InventoryRequestBuilderInterface'
                );
            }
        }
    }

    /**
     * Get the inventory request builder for the source selection algorythm
     *
     * @param string $code
     * @return \Magento\InventoryShippingAdminUi\Model\InventoryRequestBuilderFromOrderInterface
     * @throws NoSuchEntityException
     */
    public function execute(string $code): InventoryRequestBuilderFromOrderInterface
    {
        if (!isset($this->inventoryRequestBuilders[$code])) {
            throw new NoSuchEntityException(__('Unknown inventory request builder %1', $code));
        }

        return $this->inventoryRequestBuilders[$code];
    }
}
