<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryShipping\Model\PriorityShippingAlgorithm\PriorityShippingAlgorithm;

/**
 * @inheritdoc
 */
class ShippingAlgorithmProvider implements ShippingAlgorithmProviderInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function execute(): ShippingAlgorithmInterface
    {
        return $this->objectManager->get(PriorityShippingAlgorithm::class);
    }
}
