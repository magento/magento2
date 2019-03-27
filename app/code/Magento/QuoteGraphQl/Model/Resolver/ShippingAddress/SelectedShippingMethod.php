<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class SelectedShippingMethod implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $address = $value['model'];

        if ($address->getShippingMethod()) {
            list($carrierCode, $methodCode) = explode('_', $address->getShippingMethod(), 2);
            $shippingAmount = $address->getShippingAmount();
        }

        return [
            'carrier_code' => $carrierCode ?? null,
            'method_code' => $methodCode ?? null,
            'label' => $address->getShippingDescription(),
            'amount' => $shippingAmount ?? null,
        ];
    }
}
