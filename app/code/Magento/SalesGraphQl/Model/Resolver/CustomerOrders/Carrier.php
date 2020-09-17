<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CustomerOrders;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\Order;
use Magento\Shipping\Model\Config\Source\Allmethods;

/**
 * Resolve shipping carrier for order
 */
class Carrier implements ResolverInterface
{
    /**
     * @var Allmethods
     */
    private $carrierMethods;

    /**
     * @param Allmethods $carrierMethods
     */
    public function __construct(Allmethods $carrierMethods)
    {
        $this->carrierMethods = $carrierMethods;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model']) && !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Order $order */
        $order = $value['model'];
        $methodCode = $order->getShippingMethod();
        if (null === $methodCode) {
            return null;
        }

        return $this->findCarrierByMethodCode($methodCode);
    }

    /**
     * Find carrier name by shipping method code
     *
     * @param string $methodCode
     * @return string
     */
    private function findCarrierByMethodCode(string $methodCode): ?string
    {
        $allCarrierMethods = $this->carrierMethods->toOptionArray();

        foreach ($allCarrierMethods as $carrierMethods) {
            $carrierLabel = $carrierMethods['label'];
            $carrierMethodOptions = $carrierMethods['value'];
            if (is_array($carrierMethodOptions)) {
                foreach ($carrierMethodOptions as $option) {
                    if ($option['value'] === $methodCode) {
                        return $carrierLabel;
                    }
                }
            }
        }
        return null;
    }
}
