<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\Address\ShippingMethodsDataProvider;

/**
 * @inheritdoc
 */
class CartAddressShippingMethods implements ResolverInterface
{
    /**
     * @var ShippingMethodsDataProvider
     */
    private $shippingMethodsDataProvider;

    /**
     * @param ShippingMethodsDataProvider $shippingMethodsDataProvider
     */
    public function __construct(
        ShippingMethodsDataProvider $shippingMethodsDataProvider
    ) {
        $this->shippingMethodsDataProvider = $shippingMethodsDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" values should be specified'));
        }

        return $this->shippingMethodsDataProvider->getAvailableShippingMethods($value['model']);
    }
}
