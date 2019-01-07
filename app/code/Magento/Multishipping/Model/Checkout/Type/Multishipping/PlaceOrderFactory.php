<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Framework\ObjectManagerInterface;

/**
 * Creates instance of place order service according to payment provider.
 */
class PlaceOrderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PlaceOrderPool
     */
    private $placeOrderPool;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param PlaceOrderPool $placeOrderPool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        PlaceOrderPool $placeOrderPool
    ) {
        $this->objectManager = $objectManager;
        $this->placeOrderPool = $placeOrderPool;
    }

    /**
     * @param string $paymentProviderCode
     * @return PlaceOrderInterface
     */
    public function create(string $paymentProviderCode): PlaceOrderInterface
    {
        $service = $this->placeOrderPool->get($paymentProviderCode);
        if ($service === null) {
            $service = $this->objectManager->get(PlaceOrderDefault::class);
        }

        return $service;
    }
}
