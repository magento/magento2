<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Contains place order services according to payment provider.
 *
 * Can be used as extension point for changing order placing logic during multishipping checkout flow.
 */
class PlaceOrderPool
{
    /**
     * @var PlaceOrderInterface[] | TMap
     */
    private $services;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $services
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $services = []
    ) {
        $this->services = $tmapFactory->createSharedObjectsMap(
            [
                'array' => $services,
                'type' => PlaceOrderInterface::class
            ]
        );
    }

    /**
     * Returns place order service for defined payment provider.
     *
     * @param string $paymentProviderCode
     * @return PlaceOrderInterface|null
     */
    public function get(string $paymentProviderCode)
    {
        if (!isset($this->services[$paymentProviderCode])) {
            return null;
        }

        return $this->services[$paymentProviderCode];
    }
}
