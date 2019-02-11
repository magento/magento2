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
use Magento\QuoteGraphQl\Model\Cart\PaymentMethod\AvailablePaymentMethodsDataProvider;

/**
 * Get list of active payment methods resolver.
 */
class AvailablePaymentMethodsResolver implements ResolverInterface
{
    /**
     * @var AvailablePaymentMethodsDataProvider
     */
    private $addressDataProvider;

    /**
     * @param AvailablePaymentMethodsDataProvider $addressDataProvider
     */
    public function __construct(
        AvailablePaymentMethodsDataProvider $addressDataProvider
    ) {
        $this->addressDataProvider = $addressDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $cart = $value['model'];

        return $this->addressDataProvider->getPaymentMethods($cart);
    }
}
